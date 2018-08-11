<?php

namespace BfwSql\Helpers\test\unit;

use \Atoum;

$vendorPath = realpath(__DIR__.'/../../../../vendor');
require_once($vendorPath.'/autoload.php');

class Quoting extends Atoum
{
    use \BfwSql\Test\Helpers\CreateModule;
    
    protected $mock;
    
    public function beforeTestMethod($testMethod)
    {
        $this->initModule();
        $this->createSqlConnect('myBase');
        
        if ($testMethod === 'testConstructAndGetSqlConnect') {
            return;
        }
        
        $this->mock = new \mock\BfwSql\Helpers\Quoting(
            \BfwSql\Helpers\Quoting::QUOTE_ALL,
            $this->sqlConnect
        );
    }
    
    public function testConstructAndGetSqlConnect()
    {
        $this->assert('test Helpers\Quoting::__construct')
            ->object($this->mock = new \mock\BfwSql\Helpers\Quoting(
            \BfwSql\Helpers\Quoting::QUOTE_ALL,
            $this->sqlConnect
        ))
                ->isInstanceOf('\BfwSql\Helpers\Quoting')
        ;
        
        $this->assert('test Helpers\Quoting::getSqlConnect')
            ->object($this->mock->getSqlConnect())
                ->isIdenticalTo($this->sqlConnect)
        ;
    }
    
    public function testGetQuoteStatus()
    {
        $this->assert('test Helpers\Quoting::getQuoteStatus for default value')
            ->variable($this->mock->getQuoteStatus())
                ->isEqualTo(\BfwSql\Helpers\Quoting::QUOTE_ALL)
        ;
    }
    
    public function testGetPartiallyPreferedMode()
    {
        $this->assert('test Helpers\Quoting::getPartiallyPreferedMode for default value')
            ->variable($this->mock->getPartiallyPreferedMode())
                ->isEqualTo(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_QUOTE)
        ;
    }
    
    public function testGetQuotedColumns()
    {
        $this->assert('test Helpers\Quoting::getQuotedColumns for default value')
            ->array($this->mock->getQuotedColumns())
                ->isEmpty()
        ;
    }
    
    public function testGetNotQuotedColumns()
    {
        $this->assert('test Helpers\Quoting::getNotQuotedColumns for default value')
            ->array($this->mock->getNotQuotedColumns())
                ->isEmpty()
        ;
    }
    
    public function testAddQuotedColumns()
    {
        $this->assert('test Helpers\Quoting::addQuotedColumns for new columns')
            ->array($this->mock->getQuotedColumns())
                ->isEmpty()
            ->object($this->mock->addQuotedColumns(['lib', 'type']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getQuotedColumns())
                ->isEqualTo([
                    'lib' => true,
                    'type' => true
                ])
        ;
        
        $this->assert('test Helpers\Quoting::addQuotedColumns with a non-quoted column')
            ->given($setNotQuotedColumns = function($notQuotedColumns) {
                $this->notQuotedColumns = $notQuotedColumns;
            })
            ->if($setNotQuotedColumns->call($this->mock, ['used' => true]))
            ->then
            ->exception(function() {
                $this->mock->addQuotedColumns(['dateCreate', 'used', 'dateEdit']);
            })
                ->hasCode(\BfwSql\Helpers\Quoting::ERR_COLUMN_ALREADY_DEFINE_NOT_QUOTED)
            ->array($this->mock->getQuotedColumns())
                ->isEqualTo([
                    'lib'        => true,
                    'type'       => true,
                    'dateCreate' => true
                ])
        ;
    }
    
    public function testAddNotQuotedColumns()
    {
        $this->assert('test Helpers\Quoting::addNotQuotedColumns for new columns')
            ->array($this->mock->getNotQuotedColumns())
                ->isEmpty()
            ->object($this->mock->addNotQuotedColumns(['lib', 'type']))
                ->isIdenticalTo($this->mock)
            ->array($this->mock->getNotQuotedColumns())
                ->isEqualTo([
                    'lib' => true,
                    'type' => true
                ])
        ;
        
        $this->assert('test Helpers\Quoting::addNotQuotedColumns with a quoted column')
            ->given($setQuotedColumns = function($quotedColumns) {
                $this->quotedColumns = $quotedColumns;
            })
            ->if($setQuotedColumns->call($this->mock, ['used' => true]))
            ->then
            ->exception(function() {
                $this->mock->addNotQuotedColumns(['dateCreate', 'used', 'dateEdit']);
            })
                ->hasCode(\BfwSql\Helpers\Quoting::ERR_COLUMN_ALREADY_DEFINE_QUOTED)
            ->array($this->mock->getNotQuotedColumns())
                ->isEqualTo([
                    'lib'        => true,
                    'type'       => true,
                    'dateCreate' => true
                ])
        ;
    }
    
    public function testQuoteValue()
    {
        $this->assert('test Helpers\Quoting::quoteValue - prepare')
            ->if($this->calling($this->sqlConnect->getPDO())->quote = function($str) {
                return '\''.$str.'\'';
            })
            ->given($setQuoteStatus = function($quoteStatus) {
                $this->quoteStatus = $quoteStatus;
            })
            ->given($setQuotedColumns = function($quotedColumns) {
                $this->quotedColumns = $quotedColumns;
            })
            ->given($setNotQuotedColumns = function($notQuotedColumns) {
                $this->notQuotedColumns = $notQuotedColumns;
            })
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=none')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_NONE))
            ->then
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=all')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_ALL))
            ->then
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
            ->integer($this->mock->quoteValue('answer', 42))
                ->isEqualTo(42)
        ;
        
        //Test cases : https://github.com/bulton-fr/bfw-sql/issues/41#issuecomment-389154260
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=quoted; quotedColumns and notQuotedColumns empty')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_QUOTE))
            ->and($setQuotedColumns->call($this->mock, []))
            ->and($setNotQuotedColumns->call($this->mock, []))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=quoted; quotedColumns empty and into notQuotedColumns')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_QUOTE))
            ->and($setQuotedColumns->call($this->mock, []))
            ->and($setNotQuotedColumns->call($this->mock, ['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=quoted; into quotedColumns and notQuotedColumns empty')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_QUOTE))
            ->and($setQuotedColumns->call($this->mock, ['lib' => true]))
            ->and($setNotQuotedColumns->call($this->mock, []))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=quoted; into quotedColumns and notQuotedColumns')
            /*
             * Is not possible with current system.
             * One of the methods addQuotedColumns or addNotQuotedColumns will
             * throw an Exception.
             * 
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_QUOTE))
            ->and($setQuotedColumns->call($this->mock, ['lib' => true]))
            ->and($setNotQuotedColumns->call($this->mock, ['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
            */
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=not-quoted; quotedColumns and notQuotedColumns empty')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_NOTQUOTE))
            ->and($setQuotedColumns->call($this->mock, []))
            ->and($setNotQuotedColumns->call($this->mock, []))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=not-quoted; quotedColumns empty and into notQuotedColumns')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_NOTQUOTE))
            ->and($setQuotedColumns->call($this->mock, []))
            ->and($setNotQuotedColumns->call($this->mock, ['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('atoum')
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=not-quoted; into quotedColumns and notQuotedColumns empty')
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_NOTQUOTE))
            ->and($setQuotedColumns->call($this->mock, ['lib' => true]))
            ->and($setNotQuotedColumns->call($this->mock, []))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
                ->isEqualTo('\'atoum\'')
        ;
        
        $this->assert('test Helpers\Quoting::quoteValue - status=partial; partial=not-quoted; into quotedColumns and notQuotedColumns')
            /*
             * Is not possible with current system.
             * One of the methods addQuotedColumns or addNotQuotedColumns will
             * throw an Exception.
             * 
            ->if($setQuoteStatus->call($this->mock, \BfwSql\Helpers\Quoting::QUOTE_PARTIALLY))
            ->and($this->mock->setPartiallyPreferedMode(\BfwSql\Helpers\Quoting::PARTIALLY_MODE_NOTQUOTE))
            ->and($setQuotedColumns->call($this->mock, ['lib' => true]))
            ->and($setNotQuotedColumns->call($this->mock, ['lib' => true]))
            ->then
            
            ->string($this->mock->quoteValue('lib', 'atoum'))
            */
        ;
    }
}