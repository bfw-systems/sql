<?php

namespace BfwSql\Queries\Parts;

class WhereList extends CommonList
{
    /**
     * {@inheritdoc}
     */
    protected $partPrefix = 'WHERE';
    
    /**
     * {@inheritdoc}
     */
    protected $separator = ' AND ';
    
    /**
     * Magic method __invoke, used when the user call object like a function
     * @link http://php.net/manual/en/language.oop5.magic.php#object.invoke
     * 
     * @param string $expr The expression
     * @param array|null $preparedParams (default: null) params to use for this
     *  expression in case of prepared request.
     * 
     * @return void
     */
    public function __invoke(string $expr, $preparedParams = null)
    {
        $this->invokeCheckIsDisabled();
        
        parent::__invoke($expr);
        
        if ($preparedParams !== null) {
            $this->querySystem->addPreparedParams($preparedParams);
        }
    }
}
