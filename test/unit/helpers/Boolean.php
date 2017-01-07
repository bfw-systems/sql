<?php

namespace BFW\test\helpers;

/**
 * Trait to add test method for booleans
 */
trait Boolean
{
    /**
     * Test a boolean type to check the response for many input value type
     * 
     * @param string $setter The set method/property name to use to edit value
     * @param string $getter The get method/property name to use to check value
     * 
     * @return void
     */
    protected function testSetBooleans($setter, $getter, $instanceOf)
    {
        $this->testSetBoolean($setter, $getter, $instanceOf, true, true);
        $this->testSetBoolean($setter, $getter, $instanceOf, false, false);
        $this->testSetBoolean($setter, $getter, $instanceOf, 'unit', true);
        $this->testSetBoolean($setter, $getter, $instanceOf, 0, false);
        $this->testSetBoolean($setter, $getter, $instanceOf, 2, true);
        $this->testSetBoolean($setter, $getter, $instanceOf, null, false);
    }
    
    /**
     * Test a boolean property
     * Set the property with a new value
     * Get the property value and check if value is the excepted value
     * 
     * @param string  $setter   The set method/property name to use to edit value
     * @param string  $getter   The get method/property name to use to check value
     * @param mixed   $value    The new value
     * @param boolean $expected The excepted value after call the setter
     * 
     * @return void
     */
    protected function testSetBoolean(
        $setter,
        $getter,
        $instanceOf,
        $value,
        $expected
    ) {
        $expectedMethod = 'isTrue';
        if ($expected === false) {
            $expectedMethod = 'isFalse';
        }
        
        $this->assert(
                'Call '.get_class($this->class).'::'.$setter.' with a '
                .gettype($value).' '.(string) $value.' value'
            )
            ->object($this->booleanSetValue($setter, $value))
                ->isInstanceOf($instanceOf)
                ->isEqualTo($this->class)
            ->boolean($this->booleanGetValue($getter))
                ->{$expectedMethod}();
    }
    
    /**
     * Find the way to set a new value
     * Check if the way indicated is a method or a direct property
     * 
     * @param string $setter   The set method/property name to use to edit value
     * @param mixed  $newValue The new value
     * 
     * @return mixed
     * 
     * @throws Exception If the setter is not a method or a property
     */
    protected function booleanSetValue($setter, $newValue)
    {
        if (method_exists($this->class, $setter)) {
            return $this->class->{$setter}($newValue);
        } elseif (property_exists($this->class, $setter)) {
            return $this->class->{$setter} = $newValue;
        }
        
        throw new Exception('boolean setter not found');
    }
    
    /**
     * Find the way to get a new value
     * Check if the way indicated is a method or a direct property
     * 
     * @param $getter The get method/property name to use to check value
     * 
     * @return mixed
     * 
     * @throws Exception If the getter is not a method or a property
     */
    protected function booleanGetValue($getter)
    {
        if (method_exists($this->class, $getter)) {
            return $this->class->{$getter}();
        } elseif (property_exists($this->class, $getter)) {
            return $this->class->{$getter};
        }
        
        throw new Exception('boolean getter not found');
    }
}
