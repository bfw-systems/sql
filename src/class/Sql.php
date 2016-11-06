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
    public function __construct($sqlConnect)
    {
        $this->sqlConnect = $sqlConnect;
        $this->prefix     = $sqlConnect->getConnectionInfos()->tablePrefix;
    }
    
    /**
     * Getter to the property sqlConnect
     * 
     * @return \BFWSql\SqlConnect
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
    public function getLastInsertedId($name=null)
    {
        return (int) $this->PDO->lastInsertId($name);
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
    public function getLastInsertedIdWithoutAI(
        $table,
        $colId,
        $order,
        $where=''
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
    public function select($type='array')
    {
        return new SqlSelect($this->sqlConnect, $type);
    }
    
    /**
     * Return a new instance of SqlInsert
     * 
     * @param string $table   The table concerned by the request
     * @param array  $columns (default: null) All datas to add
     *  Format is array('columnName' => 'value', ...);
     * 
     * @return \BfwSql\SqlInsert
     */
    public function insert($table, $columns=null)
    {
        return new SqlInsert($this->sqlConnect, $table, $columns);
    }
    
    /**
     * Return a new instance of SqlUpdate
     * 
     * @param string $table   The table concerned by the request
     * @param array  $columns (default: null) All datas to update
     *  Format is array('columnName' => 'newValue', ...);
     * 
     * @return \BfwSql\SqlUpdate
     */
    public function update($table, $columns=null)
    {
        return new SqlUpdate($this->sqlConnect, $table, $columns);
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
        return new SqlDelete($this->sqlConnect, $table);
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
    public function create_id($table, $column)
    {
        $req = $this->select()
                    ->from($table, $column)
                    ->order($column.' ASC')
                    ->limit(1);
        
        $res = $req->fetchRow();
        $req->closeCursor();
        
        if (!$res) {
            return 1;
        }
        
        if ($res[$column] > 1) {
            return $res[$column]-1;
        }
        
        $req2 = $this->select()
                    ->from($table, $column)
                    ->order($column.' DESC')
                    ->limit(1);
        
        $res2 = $req2->fetchRow();
        $req2->closeCursor();

        return $res2[$column]+1;
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
        
        $req   = $this->sqlConnect->PDO->query($request);
        $error = $this->sqlConnect->PDO->errorInfo();
        
        if(
            !$req
            && $error[0] != null
            && $error[0] != '00000'
            && isset($error[2])
        ) {
            throw new Exception($error[2]);
        }
        
        return $req;
    }
}
