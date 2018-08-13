<?php

namespace BfwSql\Observers;

use \Exception;
use \BfwSql\Queries\AbstractQuery;
use \BfwSql\Queries\Select;

/**
 * Requests observer.
 * Create a log with SELECT requests executed and informations about it. Run an
 * EXPLAIN query on the request and add informations retured to log.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Explain extends Basic
{
    /**
     * @const EXPLAIN_OK Explain status when the request has succeeded
     */
    const EXPLAIN_OK = 'ok';
    
    /**
     * @const EXPLAIN_FAILED Explain status when the request has failed
     */
    const EXPLAIN_FAILED = 'failed';
    
    /**
     * @const EXPLAIN_EMPTY Explain status when the request has returned nothing
     */
    const EXPLAIN_EMPTY = 'empty';
    
    /**
     * @var \BfwSql\Sql The sql instanced used to run EXPLAIN request
     */
    protected $sql;
    
    /**
     * @var object An object with explain status and informations returned
     */
    protected $explain;
    
    /**
     * Getter accessor to property sql
     * 
     * @return \BfwSql\Sql
     */
    public function getSql(): \BfwSql\Sql
    {
        return $this->sql;
    }
    
    /**
     * Getter accessor to property explain
     * 
     * @return object
     */
    public function getExplain()
    {
        return $this->explain;
    }
    
    /**
     * {@inheritdoc}
     * Limited to "system query" event.
     */
    protected function analyzeUpdate()
    {
        if ($this->action === 'system query') {
            $this->systemQuery();
        }
    }
    
    /**
     * {@inheritdoc}
     * Limited to \BfwSql\Queries\Select object
     * We not run EXPLAIN automatically on other request type to not destroy db
     * 
     * @return void
     */
    protected function systemQuery()
    {
        if ($this->context instanceof AbstractQuery === false) {
            throw new Exception(
                '"system query" event should have an AbstractQuery class'
                .' into the context.',
                self::ERR_SYSTEM_QUERY_CONTEXT_CLASS
            );
        }
        
        if ($this->context instanceof Select === false) {
            return;
        }
        
        $this->explain = new class {
            public $status = \BfwSql\Observers\Explain::EXPLAIN_OK;
            public $datas  = [];
        };
        
        $this->sql = $this->obtainSql();
        
        $this->runExplain();
        return parent::systemQuery();
    }
    
    /**
     * Obtain a \BfwSql\Sql instance.
     * 
     * @return \BfwSql\Sql
     */
    protected function obtainSql(): \BfwSql\Sql
    {
        $sqlConnect = $this->context->getSqlConnect();
        return new \BfwSql\Sql($sqlConnect);
    }
    
    /**
     * Run the EXPLAIN request
     * 
     * @return void
     */
    protected function runExplain()
    {
        $this->sql->query('FLUSH STATUS;');
        $pdo = $this->sql->getSqlConnect()->getPDO();
        
        $explainQuery = 'EXPLAIN '.$this->context->assemble();
        $request      = $pdo->prepare(
            $explainQuery,
            $this->context->getExecuter()->getPrepareDriversOptions()
        );
        $explainResult = $request->execute(
            $this->context->getPreparedParams()
        );
        
        if ($explainResult === false) {
            $this->explain->status = self::EXPLAIN_FAILED;
            return;
        }
        
        $explainFetch = $request->fetch(\PDO::FETCH_ASSOC);
        if ($explainFetch === false) {
            $this->explain->status = self::EXPLAIN_EMPTY;
            return;
        }
        
        foreach ($explainFetch as $explainKey => $explainValue) {
            $this->explain->datas[$explainKey] = $explainValue;
        }
    }
    
    /**
     * {@inheritdoc}
     * Add explain informations to monolog too.
     */
    protected function addQueryToMonoLog(string $query, array $error)
    {
        $this->monolog->getLogger()->debug(
            'Type: '.$this->action.' ; '
            .'Query: '.$query. ' ; '
            .'Errors: '.print_r($error, true).' ; '
            .'Explain status: '.$this->explain->status.' ; '
            .'Explain datas: '.print_r($this->explain->datas, true)
        );
    }
}
