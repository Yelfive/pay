<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2018-07-04
 */

namespace fk\pay\lib\AliPay\result;

abstract class ResultContract
{
    public function __construct($properties)
    {
        $this->configure($properties);
    }

    protected function configure($properties)
    {
        foreach ($properties as $property => $value) {
            $method = str_replace('_', '', "set_$property");
            if (method_exists($this, $method)) $this->$method($value);
            else $this->$property = $value;
        }
    }

    public function isSuccessful(): bool
    {
        return isset($this->code) && $this->code == 10000;
    }
}