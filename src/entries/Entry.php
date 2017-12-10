<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\Exception;

abstract class Entry implements EntryInterface
{

    protected $notifyUrl;

    protected $returnUrl;

    protected $config;

    protected $app;

    public function setConfig(array $config): EntryInterface
    {
        $this->config = $config;
        $this->notifyUrl = $config['notify_url'] ?? '';
        $this->returnUrl = $config['return_url'] ?? '';
        return $this;
    }

    public function setNotifyUrl(string $url): EntryInterface
    {
        $this->notifyUrl = $url;
        return $this;
    }

    public function setReturnUrl(string $url): EntryInterface
    {
        $this->returnUrl = $url;
        return $this;
    }

    protected function required($data, $attributes, string $message = null)
    {
        if ($message === null) $message = '"{attribute}" is required';
        foreach ($attributes as $name) {
            if (empty($data[$name])) throw new Exception(str_replace('{attribute}', $name, $message));
        }
        return true;
    }

    public function app($name)
    {
        $this->app = $name;
    }

}