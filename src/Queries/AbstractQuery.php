<?php

namespace BfwSql\Queries;

use \Exception;

/**
 * Abstract class used for all query writer class.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 * 
 * @method \BfwSql\Queries\AbstractQuery table(string|array $nameInfos, string|array|null $columns=null)
 * @method \BfwSql\Queries\AbstractQuery where(string $expr, array|null $preparedParams = null)
 */
abstract class AbstractQuery
{
    /**
     * @const ERR_ASSEMBLE_MISSING_TABLE_NAME Exception code if the user
     * try to generate a request without table name.
     */
    const ERR_ASSEMBLE_MISSING_TABLE_NAME = 2401001;
    
    /**
     * @const ERR_CALL_UNKNOWN_METHOD Exception code if the __call method
     * receive a method which is really unknown
     */
    const ERR_CALL_UNKNOWN_METHOD = 2401002;
    
    /**
     * @const ERR_ASSEMBLE_EMPTY_PART Exception code if during the query
     * assemble, a part is empty but should not be empty.
     */
    const ERR_ASSEMBLE_EMPTY_PART = 2401003;
    
    /**
     * @var \BfwSql\SqlConnect $sqlConnect SqlConnect object
     */
    protected $sqlConnect;
    
    /**
     * @var \BfwSql\Executers\Common $executer
     */
    protected $executer;
    
    /**
     * @var string $assembledRequest The request will be executed
     */
    protected $assembledRequest = '';
    
    /**
     * @var string[] $preparedParams Arguments used by prepared request
     */
    protected $preparedParams = [];
    
    /**
     * @var array $queriesParts All parts used to generate the final request
     */
    protected $queriesParts = [];
    
    /**
     * Constructor
     * 
     * @param \BfwSql\SqlConnect $sqlConnect Instance of SGBD connexion
     */
    public function __construct(\BfwSql\SqlConnect $sqlConnect)
    {
        $this->sqlConnect = $sqlConnect;
        
        $usedClass      = \BfwSql\UsedClass::getInstance();
        $executerClass  = $usedClass->obtainClassNameToUse('ExecutersCommon');
        $this->executer = new $executerClass($this);
        
        $this->defineQueriesParts();
    }
    
    /**
     * Define all part which will be necessary to generate the final request
     * 
     * @return void
     */
    protected function defineQueriesParts()
    {
        $this->queriesParts = [
            'table' => new Parts\Table($this),
            'where' => new Parts\WhereList($this)
        ];
    }
    
    /**
     * Magic method __call, used when the user call a non-existing method
     * @link http://php.net/manual/en/language.oop5.overloading.php#object.call
     * 
     * @param string $name the name of the method being called
     * @param array $args an enumerated array containing the parameters
     *  passed to the $name'ed method.
     * 
     * @return object The current instance if the part object have the method
     * __invoke(), or directly the part object asked
     * 
     * @throws Exception If the part not exist
     */
    public function __call(string $name, array $args)
    {
        if (!isset($this->queriesParts[$name])) {
            throw new Exception(
                'Unknown called method '.$name,
                self::ERR_CALL_UNKNOWN_METHOD
            );
        }
        
        if (!method_exists($this->queriesParts[$name], '__invoke')) {
            return $this->queriesParts[$name];
        }
        
        $this->queriesParts[$name](...$args);
        return $this;
    }
    
    /**
     * Getter to access at sqlConnect property
     * 
     * @return \BfwSql\SqlConnect
     */
    public function getSqlConnect(): \BfwSql\SqlConnect
    {
        return $this->sqlConnect;
    }
    
    /**
     * Getter accessor to property executer
     * 
     * @return \BfwSql\Executers\Common
     */
    public function getExecuter(): \BfwSql\Executers\Common
    {
        return $this->executer;
    }
    
    /**
     * Getter accessor to property queriesParts
     * 
     * @return array
     */
    public function getQueriesParts(): array
    {
        return $this->queriesParts;
    }
    
    /**
     * Getter to access to assembledRequest property
     * 
     * @return string
     */
    public function getAssembledRequest(): string
    {
        return $this->assembledRequest;
    }
    
    /**
     * Getter to access at preparedParams property
     * 
     * @return array
     */
    public function getPreparedParams(): array
    {
        return $this->preparedParams;
    }
    
    /**
     * Check if a request is assemble or not.
     * If not, run the method assembleRequest.
     * 
     * @return boolean
     */
    public function isAssembled(): bool
    {
        if ($this->assembledRequest === '') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Write the query
     * 
     * @return void
     */
    protected function assembleRequest()
    {
        if (empty($this->queriesParts['table']->getName())) {
            throw new Exception(
                'The main table of the request should be declared.',
                self::ERR_ASSEMBLE_MISSING_TABLE_NAME
            );
        }
        
        $generateOrder = $this->obtainGenerateOrder();
        
        $this->assembledRequest = '';
        
        foreach ($generateOrder as $partName => $partInfos) {
            $requestPart = $this->assembleRequestPart(
                $partName,
                $partInfos
            );
            
            if ($requestPart !== '') {
                //To avoid many blank lines into generated request.
                $this->assembledRequest .= $requestPart."\n";
            }
        }
    }
    
    /**
     * Define the order to call all queries part.
     * Each item contain an array with somes key to define some how generate
     * the query part.
     * Properties is:
     * * callback: The callback to call to generate the query part
     * * canBeEmpty : If the part can be empty, or not
     * * prefix : The prefix to add before the generated sql
     * * usePartPrefix : If the prefix should be added before the generated sql
     * 
     * @return array
     */
    protected abstract function obtainGenerateOrder(): array;
    
    /**
     * Generate the sql query for a part
     * 
     * @param string $partName The part name
     * @param array $partInfos Infos about the generation of the sql part
     * 
     * @return string
     * 
     * @throws \Exception If the sql generated is empty but should not be
     */
    protected function assembleRequestPart(
        string $partName,
        array $partInfos
    ): string {
        $this->addMissingKeysToPartInfos($partName, $partInfos);
        
        $generateSqlPart = '';

        if (isset($partInfos['callback'])) {
            $generateSqlPart = $partInfos['callback']();
        }
        
        if (empty($generateSqlPart)) {
            if ($partInfos['canBeEmpty'] === false) {
                throw new Exception(
                    'The part '.$partName.' should not be empty.',
                    self::ERR_ASSEMBLE_EMPTY_PART
                );
            }
            
            return '';
        }
        
        if ($partInfos['usePartPrefix'] === true) {
            return $partInfos['prefix'].' '.$generateSqlPart;
        }
        
        return $generateSqlPart;
    }
    
    /**
     * Check each item of the array containing generation infos for a sql part
     * and add missing keys
     * 
     * @param string $partName The part name
     * @param array &$partInfos Infos about the generation of the sql part
     * 
     * @return void
     */
    protected function addMissingKeysToPartInfos(
        string $partName,
        array &$partInfos
    ) {
        $defaultValues = $this->obtainPartInfosDefaultValues($partName);
        
        if (!isset($partInfos['callback'])) {
            $partInfos['callback'] = [$defaultValues, 'generate'];
        }
        
        if (!isset($partInfos['prefix'])) {
            $partInfos['prefix'] = $defaultValues->getPartPrefix();
        }
        
        if (!isset($partInfos['usePartPrefix'])) {
            $partInfos['usePartPrefix'] = $defaultValues->getUsePartPrefix();
        }
        
        if (!isset($partInfos['canBeEmpty'])) {
            $partInfos['canBeEmpty'] = $defaultValues->getCanBeEmpty();
        }
    }
    
    /**
     * Return the object containing all default value for a part infos.
     * Used by the method who add missing key into the part infos array.
     * If the part not exist into the array queriesParts, use an anonymous
     * class who extend AbstractPart to have all method and property with
     * their default values.
     * 
     * @param string $partName The part name
     * 
     * @return \BfwSql\Queries\Parts\AbstractPart
     */
    protected function obtainPartInfosDefaultValues(
        string $partName
    ): Parts\AbstractPart {
        if (
            isset($this->queriesParts[$partName]) &&
            $this->queriesParts[$partName] instanceof Parts\AbstractPart
        ) {
            return $this->queriesParts[$partName];
        }
        
        return new class($this) extends Parts\AbstractPart {
            public function generate(): string
            {
                return '';
            }
        };
    }
    
    /**
     * Return the assembled request
     * 
     * @param boolean $force : Force to re-assemble request
     * 
     * @return string
     */
    public function assemble(bool $force = false): string
    {
        if ($this->isAssembled() === false || $force === true) {
            $this->assembleRequest();
        }
        
        return $this->assembledRequest;
    }
    
    /**
     * Execute the assembled request
     * 
     * @throws \Exception If the request fail
     * 
     * @return \PDOStatement|integer
     */
    public function execute()
    {
        return $this->executer->execute();
    }
    
    /**
     * To call this own request without use query writer
     * 
     * @param string $request The user request
     * 
     * @return $this
     */
    public function query(string $request): self
    {
        $this->assembledRequest = $request;
        
        return $this;
    }
    
    /**
     * Add filters to prepared requests
     * 
     * @param array $preparedParams Filters to add in prepared request
     * 
     * @return $this
     */
    public function addPreparedParams(array $preparedParams): self
    {
        foreach ($preparedParams as $prepareKey => $prepareValue) {
            $this->preparedParams[$prepareKey] = $prepareValue;
        }
        
        return $this;
    }
}
