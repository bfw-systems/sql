<?php

namespace BfwSql;

use \Exception;

/**
 * Class to access to query writer
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Sql
{
    /**
     * @const ERR_QUERY_BAD_REQUEST Exception code if the request executed
     * on query method have an error.
     */
    const ERR_QUERY_BAD_REQUEST = 2103001;
    
    /**
     * @var \BfwSql\SqlConnect $sqlConnect SqlConnect object
     */
    protected $sqlConnect;
    
    /**
     * @var string $prefix Tables prefix
     */
    protected $prefix = '';
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect SqlConnect instance
     * 
     * @throws \Exception
     */
    public function __construct(\BfwSql\SqlConnect $sqlConnect)
    {
        $this->sqlConnect = $sqlConnect;
        $this->prefix     = $sqlConnect->getConnectionInfos()->tablePrefix;
    }
    
    /**
     * Getter to the property sqlConnect
     * 
     * @return \BfwSql\SqlConnect
     */
    public function getSqlConnect()
    {
        return $this->sqlConnect;
    }
    
    /**
     * Getter to the property prefix
     * 
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    /**
     * Get the id for the last item has been insert in database
     * 
     * @param string|null $name (default: null) Name of the sequence for the id
     *  Used for SGDB like PostgreSQL. Not use it for mysql.
     * 
     * @return integer
     */
    public function obtainLastInsertedId($name = null)
    {
        return (int) $this->sqlConnect->getPDO()->lastInsertId($name);
    }
    
    /**
     * Get the id for the last item has been insert in database for a table
     * without auto-increment
     * 
     * @param string       $table The table name
     * @param string       $colId The column name for the ID
     * @param string|array $order Columns to sort table content
     * @param string|array $where All where instruction used for filter content
     * 
     * @return integer
     */
    public function obtainLastInsertedIdWithoutAI(
        $table,
        $colId,
        $order,
        $where = ''
    ) {
        $req = $this->select()
                    ->from($table, $colId)
                    ->limit(1);
    
        if (is_array($where)) {
            foreach ($where as $val) {
                $req->where($val);
            }
        } elseif ($where != '') {
            $req->where($where);
        }
        
        if (is_array($order)) {
            foreach ($order as $val) {
                $req->order($val);
            }
        } else {
            $req->order($order);
        }
        
        $res = $req->fetchRow();
        $req->closeCursor();
        
        if ($res) {
            return (int) $res[$colId];
        }
        
        return 0;
    }
    
    /**
     * Return a new instance of SqlSelect
     * 
     * @param string $type (default: "array") Return PHP type
     *  Possible value : "array" or "object"
     * 
     * @return \BfwSql\SqlSelect
     */
    public function select($type = 'array')
    {
        $usedClass       = \BfwSql\UsedClass::getInstance();
        $selectClassName = $usedClass->obtainClassNameToUse('ActionsSelect');
        
        return new $selectClassName($this->sqlConnect, $type);
    }
    
    /**
     * Return a new instance of SqlInsert
     * 
     * @param string $table   The table concerned by the request
     * @param array  $columns (default: null) All datas to add
     *  Format is array('columnName' => 'value', ...);
     * @param string $quoteStatus (default: QUOTE_ALL) Status to automatic
     *  quoted string value system.
     * 
     * @return \BfwSql\SqlInsert
     */
    public function insert(
        $table,
        $columns = null,
        $quoteStatus = \BfwSql\Actions\AbstractActions::QUOTE_ALL
    ) {
        $usedClass       = \BfwSql\UsedClass::getInstance();
        $insertClassName = $usedClass->obtainClassNameToUse('ActionsInsert');
        
        return new $insertClassName(
            $this->sqlConnect,
            $table,
            $columns,
            $quoteStatus
        );
    }
    
    /**
     * Return a new instance of SqlUpdate
     * 
     * @param string $table   The table concerned by the request
     * @param array  $columns (default: null) All datas to update
     *  Format is array('columnName' => 'newValue', ...);
     * @param string $quoteStatus (default: QUOTE_ALL) Status to automatic
     *  quoted string value system.
     * 
     * @return \BfwSql\SqlUpdate
     */
    public function update(
        $table,
        $columns = null,
        $quoteStatus = \BfwSql\Actions\AbstractActions::QUOTE_ALL
    ) {
        $usedClass       = \BfwSql\UsedClass::getInstance();
        $updateClassName = $usedClass->obtainClassNameToUse('ActionsUpdate');
        
        return new $updateClassName(
            $this->sqlConnect,
            $table,
            $columns,
            $quoteStatus
        );
    }
    
    /**
     * Return a new instance of SqlDelete
     * 
     * @param string $table The table concerned by the request
     * 
     * @return \BfwSql\SqlDelete
     */
    public function delete($table)
    {
        $usedClass       = \BfwSql\UsedClass::getInstance();
        $deleteClassName = $usedClass->obtainClassNameToUse('ActionsDelete');
        
        return new $deleteClassName($this->sqlConnect, $table);
    }
    
    /**
     * Find the first vacant id on a table and for a column
     * 
     * @param string $table  The table concerned by the request
     * @param string $column The id column. Must be an integer..
     * 
     * @throws \Exception If a error has been throw during the search
     * 
     * @return integer
     */
    public function createId($table, $column)
    {
        //Search the first line in the table
        $reqFirstLine = $this->select()
            ->from($table, $column)
            ->order($column.' ASC')
            ->limit(1);
        
        $resFirstLine = $reqFirstLine->fetchRow();
        $reqFirstLine->closeCursor();
        
        // If nothing in the table. First AI is 1
        if (!$resFirstLine) {
            return 1;
        }
        
        // If the id for the first line is > 1
        if ($resFirstLine[$column] > 1) {
            return $resFirstLine[$column] - 1;
        }
        
        //First line have ID=1, we search from the end
        $reqLastLine = $this->select()
            ->from($table, $column)
            ->order($column.' DESC')
            ->limit(1);
        
        $resLastLine = $reqLastLine->fetchRow();
        $reqLastLine->closeCursor();

        //Get the last ID and add 1
        return $resLastLine[$column] + 1;
    }
    
    /**
     * Run the query in parameter 
     * 
     * @param string $request The request to run
     * 
     * @throws \Exception If the request has failed
     * 
     * @return \PDOStatement
     */
    public function query($request)
    {
        $this->sqlConnect->upNbQuery();
        
        $req   = $this->sqlConnect->getPDO()->query($request);
        $error = $this->sqlConnect->getPDO()->errorInfo();
        
        $app     = \BFW\Application::getInstance();
        $subject = $app->getSubjectList()->getSubjectForName('bfw-sql');
        $subject->addNotification(
            'user query',
            (object) [
                'request' => $request,
                'error'   => $error
            ]
        );
        
        if (
            !$req
            && $error[0] !== null
            && $error[0] !== '00000'
            && isset($error[2])
        ) {
            throw new Exception(
                $error[2],
                self::ERR_QUERY_BAD_REQUEST
            );
        }
        
        return $req;
    }
}
