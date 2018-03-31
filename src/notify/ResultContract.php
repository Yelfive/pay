<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2018-03-31
 */

namespace fk\pay\notify;

abstract class ResultContract
{
    public function __construct($data)
    {
        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) $this->$property = $value;
        }
    }
}