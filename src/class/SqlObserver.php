<?php

namespace BfwSql;

/**
 * Requests observer.
 * Create a log with all EXPLAIN informations about SELECT requests executed.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class SqlObserver implements \SplObserver
{
    /**
     * @var \SplFileObject $logFile The SplFileObject instance for the log file
     */
    protected $logFile;
    
    /**
     * @var \stdClass $observerConfig The "observer" part in config file
     */
    protected $observerConfig;
    
    /**
     * @var string $action The last action to send to observers
     */
    protected $action = '';
    
    /**
     * @var mixed $context The context to send to observers
     */
    protected $context = null;
    
    /**
     * Constructor
     * 
     * @param \stdClass $observerConfig The "observer" part in config file
     */
    public function __construct($observerConfig)
    {
        $this->observerConfig = $observerConfig;
        
        $this->logFile = new \SplFileObject($observerConfig->logFile, 'a+b');
    }
    
    /**
     * {@inheritdoc}
     */
    public function update(\SplSubject $subject)
    {
        $this->action  = $subject->getAction();
        $this->context = $subject->getContext();
        
        $this->analyzeUpdate();
    }
    
    /**
     * Analyze the update sent by subjects to search if the notify is for us
     * 
     * @return void
     */
    protected function analyzeUpdate()
    {
        if ($this->action !== 'BfwSqlRequest') {
            return;
        }
        
        if (get_class($this->context) !== '\BfwSql\SqlSelect') {
            return;
        }
        
        $this->runRequests();
    }
    
    /**
     * Run all requests to get informations about the SELECT request
     * 
     * @return void
     */
    protected function runRequests()
    {
        $this->addToLogFile('************* DEBUT OPTIMIZE SQL *************');
        $this->addToLogFile('BackTrace   : '.print_r($this->obtainBackTrace(), true));
        $this->addToLogFile('Requête SQL : '.$this->context->assemble());
        
        $sqlConnect = $this->context->getSqlConnect();
        $requestObj = new Sql($sqlConnect);
        
        $this->runExplain($requestObj);
        $this->runShowStatus($requestObj);
        
        $this->addToLogFile('************* FIN OPTIMIZE SQL *************');
        $this->addEmptyLineToLogFile();
    }
    
    /**
     * Obtain the backtrace to know informations about the caller of the
     * sql request.
     * 
     * @return string[]
     */
    protected function obtainBackTrace()
    {
        $backtrace      = [];
        $backtraceInfos = debug_backtrace();
        
        foreach ($backtraceInfos as $trace) {
            $backtrace[] = $trace['file'].' : '.$trace['line'];
        }
        
        return $backtrace;
    }
    
    /**
     * Add a message to the observer log file
     * 
     * @param string $message
     * 
     * @return void
     */
    protected function addToLogFile($message)
    {
        $dateTime    = new \DateTime;
        $messageDate = $dateTime->format('Y-m-d H:i:s');
        
        $this->logFile->fwrite('['.$messageDate.'] '.$message."\n");
    }
    
    /**
     * Add a empty line to the log file
     * 
     * @return void
     */
    protected function addEmptyLineToLogFile()
    {
        $this->logFile->fwrite("\n");
    }
    
    /**
     * Run the EXPLAIN request and send informations to log file
     * 
     * @param \BfwSql\Sql $requestObj
     * 
     * @return void
     */
    protected function runExplain(\BfwSql\Sql $requestObj)
    {
        $requestObj->query('FLUSH STATUS;');
        
        $explainQuery  = 'EXPLAIN '.$this->context->assemble();
        $explainResult = $requestObj->query($explainQuery);
        
        if ($explainResult === false) {
            $this->addToLogFile('EXPLAIN failed');
            return;
        }
        
        $explainFetchAll = $explainResult->fetchAll();
        if (!isset($explainFetchAll[0])) {
            $this->addToLogFile('EXPLAIN VIDE');
            return;
        }
        
        $explainDatas = [];
        foreach ($explainFetchAll[0] as $explainKey => $explainValue) {
            if (is_numeric($explainValue)) {
                continue;
            }
            
            $explainDatas[$explainKey] = $explainValue;
        }
        
        $this->addToLogFile(print_r($explainDatas, true));
    }
    
    /**
     * Run the SHOW STATUS request and send informations to log file
     * 
     * @param \BfwSql\Sql $requestObj
     * 
     * @return void
     */
    protected function runShowStatus(\BfwSql\Sql $requestObj)
    {
        $statusResult = $requestObj->query('SHOW STATUS');
        
        if ($statusResult === false) {
            $this->addToLogFile('SHOW STATUS failed');
            return;
        }
        
        $statusFetchAll = $statusResult->fetchAll();
        if (empty($statusFetchAll)) {
            $this->addToLogFile('EXPLAIN VIDE');
            return;
        }
        
        $statusDatas = [];
        foreach ($statusFetchAll as $statusRow) {
            $statusKey   = $statusRow['Variable_name'];
            $statusValue = $statusRow['Value'];

            if (
                substr($statusKey, 0, 8) === 'Created_'
                || substr($statusKey, 0, 8) === 'Handler_'
            ) {
                $statusDatas[$statusKey] = $statusValue;
            }
        }
        
        $this->addToLogFile(print_r($statusDatas, true));
    }
}
