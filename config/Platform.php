<?php

namespace fk\pay\config;

use fk\pay\Exception;

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */
class Platform
{

    const CHANNEL_WE_CHAT = 'WeChat';
    const CHANNEL_ALI_PAY = 'AliPay';

    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public $channel;

    /**
     * With which channel
     * @param string $channel
     * @return $this
     * @throws Exception
     */
    public function with($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @var string
     * @see app
     */
    private $_app;

    /**
     * App name for the specified channel(set by calling Platform::with())
     * @param string $name
     * @return $this
     */
    public function app($name)
    {
        $this->_app = $name;
        return $this;
    }

    public function loadConfigure()
    {
        $channel = &$this->channel;
        if ($channel == null) throw new Exception('Miss required parameter: channel');
        if (empty($this->config[$channel])) throw new Exception('No configuration under such channel: ' . $channel);

        $method = "loadConfigureOf{$channel}";
        if (method_exists($this, $method)) {
            $this->$method($this->config[$channel]);
        } else {
            throw new Exception('The payment channel is not supported yet: ' . $channel);
        }
    }

    /**
     * extract client from request
     * in the format of
     *
     * type [extra information]
     *
     * e.g.
     * - Android
     * - iOS iPhone
     * - iOS iPad
     * - web
     *      Mozilla/5.0 (Linux; U; Android 5.0.2; zh-CN; Redmi Note 3 Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 OPR/11.2.3.102637 Mobile Safari/537.36
     */
    public function getClient()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        if (strpos($agent, 'Mozilla') !== false) {
            return 'Web';
        } else {
            return 'Unknown';
        }
    }

    public function getAppName()
    {
        return $this->_app;
    }

    protected function loadConfigureOfWeChat($configure)
    {
        $class = '\fk\pay\lib\\' . strtolower($this->channel) . '\Config';
        if ($this->_app) {
            $wcApp = &$this->_app;
        } else if (method_exists(\Yii::$app->getRequest(), 'getClient')) {
            $client = \Yii::$app->getRequest()->getClient();
        } else {
            $client = $this->getClient();
        }

        if (isset($client)) {
            $wcApp = $client === 'Web' ? 'web' : 'app';
        }
        if (empty($configure[$wcApp])) throw new Exception('Configure for ' . $class . ' is required, while empty given.');

        foreach ($configure[$wcApp] as $k => &$v) {
            $property = strtoupper($k);
            if (property_exists($class, $property)) {
                if (
                    $property == 'SSL_CERT_PATH'
                    || $property == 'SSL_KEY_PATH'
                ) {
                    $v = \Yii::getAlias($v);
                }
                $class::$$property = $v;
            }
        }
        unset($v);
    }

}