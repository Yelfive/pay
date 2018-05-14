<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\Exception;
use fk\pay\notify\NotifyInterface;
use fk\pay\PlatformsConfig;

abstract class Entry implements EntryInterface
{

    protected $config;

    public $response;

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

    public function notify(callable $callback)
    {
        $rpos = strrpos(static::class, '\\');
        $start = $rpos === false ? 0 : $rpos + 1;
        $basename = str_replace('Entry', 'Notify', substr(static::class, $start));
        $className = '\fk\pay\notify\\' . $basename;
        $notify = new $className;

        if ($notify instanceof NotifyInterface) {
            return $notify::handle($callback, $this->config);
        }
        throw new Exception('Notify class should be instance of ' . NotifyInterface::class);
    }
}