<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-07
 */

namespace fk\pay\config;

use fk\helpers\ArrayHelper;
use fk\pay\Exception;

class Container
{

    /**
     * Config for all payments with its
     * key to be the payment name and value the config
     * @var array
     */
    protected $config;

    /**
     * The config of specified payment, such as config for WeChat,
     * which is declare by using `with`
     * @see with()
     * @var array
     */
    protected $configInUse = [];

    protected $with;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __get($name)
    {

    }

    public function get($name = null)
    {
        if (!$this->with) throw new Exception('Platform not specified');

        if (empty($this->config[$this->with])) throw new Exception("Config not specified for platform: {$this->with}");

        $config = $this->config[$this->with];
        if (!is_string($name)) return $config;

        return ArrayHelper::get($config, $name);
    }

    /**
     * The platform to pay with, i.e. $pay->with('WeChat')
     * @param string $platform
     * @return null|string
     */
    public function with(string $platform = null)
    {
        if ($platform === null) return $this->with;
        $this->with = $platform;
    }
}