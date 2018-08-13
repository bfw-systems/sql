<?php

namespace BfwSql\Runners;

use \Exception;

/**
 * Create and attach all observer declared in config file.
 * Create also monolog instance used by him.
 * It's a runner, so is called when the module is initialized.
 * 
 * @package bfw-sql
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */
class Observers extends AbstractRunner
{
    /**
     * @const ERR_ADD_OBSERVER_MISSING_CLASSNAME Exception code if an observer
     * declared in config has no class name
     */
    const ERR_ADD_OBSERVER_MISSING_CLASSNAME = 2604001;
    
    /**
     * @const ERR_ADD_OBSERVER_UNKNOWN_CLASS Exception code if an observer
     * declared in config has an unknown class.
     */
    const ERR_ADD_OBSERVER_UNKNOWN_CLASS = 2604002;
    
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $app     = \BFW\Application::getInstance();
        $subject = new \BFW\Subject;
        $app->getSubjectList()->addSubject($subject, 'bfw-sql');
        
        $config        = $this->module->getConfig();
        $listObservers = $config->getValue('observers', 'observers.php');
        
        foreach ($listObservers as $observerInfos) {
            $this->addObserver($observerInfos, $subject);
        }
    }
    
    /**
     * Create the new observer and attach it to the bfw-sql subject
     * 
     * @param array $observerInfos Infos from config for the new observer
     * @param \BFW\Subject $subject The subject to attach the new observer
     * 
     * @return void
     */
    protected function addObserver(array $observerInfos, \BFW\Subject $subject)
    {
        $this->checkObserverClass($observerInfos);
        $this->checkObserverMonologHandlers($observerInfos);
        
        $monolog = $this->addMonologForObserver($observerInfos);
        
        $observerClassName = $observerInfos['className'];
        $observer          = new $observerClassName($monolog);
        $subject->attach($observer);
    }
    
    /**
     * Check the observer class infos declared in config
     * 
     * @param array $observerInfos Infos from config for the new observer
     * 
     * @throws Exception If there are somes problems with the class to use
     * 
     * @return void
     */
    protected function checkObserverClass(array $observerInfos)
    {
        if (!array_key_exists('className', $observerInfos)) {
            throw new Exception(
                'The key "className" should be declared for each observer.',
                self::ERR_ADD_OBSERVER_MISSING_CLASSNAME
            );
        }
        
        if (!class_exists($observerInfos['className'])) {
            throw new Exception(
                'The class '.$observerInfos['className'].' not exist.',
                self::ERR_ADD_OBSERVER_UNKNOWN_CLASS
            );
        }
    }
    
    /**
     * Check monolog handler declared in the config and add missing datas.
     * 
     * @param array &$observerInfos Infos from config for the new observer
     * 
     * @return void
     */
    protected function checkObserverMonologHandlers(array &$observerInfos)
    {
        if (!array_key_exists('monologHandlers', $observerInfos)) {
            $observerInfos['monologHandlers'] = [];
        }
        $handlersInfos = &$observerInfos['monologHandlers'];
        
        if (!array_key_exists('useGlobal', $handlersInfos)) {
            $handlersInfos['useGlobal'] = false;
        }
        
        if (!is_bool($handlersInfos['useGlobal'])) {
            $handlersInfos['useGlobal'] = (bool) $handlersInfos['useGlobal'];
        }
        
        if (!array_key_exists('others', $handlersInfos)) {
            $handlersInfos['others'] = [];
        }
        
        if (!is_array($handlersInfos['others'])) {
            $handlersInfos['others'] = [];
        }
    }
    
    /**
     * Add monolog handlers for the new observer
     * 
     * @param array $observerInfos Infos from config for the new observer
     * 
     * @return \BFW\Monolog
     */
    protected function addMonologForObserver(array $observerInfos): \BFW\Monolog
    {
        $handlersInfos = $observerInfos['monologHandlers'];
        
        if ($handlersInfos['useGlobal'] === false) {
            $monolog = Monolog::createMonolog($this->module->getConfig());
        } else {
            //Clone because we want add new handlers only for this observer.
            $monolog = clone $this->module->monolog;
        }
        
        if ($handlersInfos['others'] !== []) {
            foreach ($handlersInfos['others'] as $handlerInfos) {
                $monolog->addNewHandler($handlerInfos);
            }
        }
        
        return $monolog;
    }
}
