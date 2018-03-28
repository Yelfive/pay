<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\Exception;
use fk\pay\PlatformsConfig;

abstract class Entry implements EntryInterface
{

    protected $config;

    public function __construct(PlatformsConfig $config)
    {
        $this->config = $config;
        $this->setConfig();
    }

    protected function setConfig()
    {
    }

    /**
     * @param array $data
     * @param array $attributes
     * @param string|null $message
     * @return bool
     * @throws Exception
     */
    protected function required(array $data, array $attributes, string $message = null)
    {
        if ($message === null) $message = '"{attribute}" is required';
        foreach ($attributes as $name) {
            if (empty($data[$name])) throw new Exception(str_replace('{attribute}', $name, $message));
        }
        return true;
    }

}