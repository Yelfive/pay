<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

abstract class Entry implements EntryInterface
{

    public $notifyUrl;

    public $returnUrl;

    public $config;

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
}