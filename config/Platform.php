<?php

namespace fk\pay\config;

use fk\pay\Exception;

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */
class Platform
{

    const WITH_WE_CHAT = 'WeChat';
    const WITH_ALI_PAY = 'AliPay';

    /**
     * @var array Configure array
     */
    public $configs;

    /**
     * @var array
     */
    public $specifyConfig;

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    public $with;

    /**
     * With which channel
     * @param string $platform
     * @return $this
     * @throws Exception
     */
    public function with($platform)
    {
        $this->with = $platform;
        return $this;
    }

    /**
     * @var string
     * @see app
     */
    private $_app;

    /**
     * App name for the specified channel(witch is set by calling Platform::with())
     * @param string $name
     * @return $this
     */
    public function app($name)
    {
        $this->_app = $name;
        return $this;
    }

    /**
     * Returns an array contains all the configure for given payment
     * @return array
     * @throws Exception
     */
    public function loadConfigure(): array
    {
        $platform = &$this->with;
        if ($platform == null) throw new Exception('Miss required parameter: platform');
        if (empty($this->configs[$platform])) throw new Exception("No configuration under such platform: $platform");

        $method = "loadConfigureOf{$platform}";
        if (method_exists($this, $method)) {
            return $this->specifyConfig = $this->$method($this->configs[$platform]);
        } else {
            throw new Exception("The payment platform is not supported yet: $platform");
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
        $class = '\fk\pay\lib\\' . strtolower($this->with) . '\Config';
        if ($this->_app) {
            $weChatApp = &$this->_app;
        } else {
            $weChatApp = $this->getClient() === 'Web' ? 'web' : 'app';
        }

        if (empty($configure[$weChatApp])) throw new Exception('Configure for ' . $class . ' is required, while empty given.');

        foreach ($configure[$weChatApp] as $k => &$v) {
            $property = strtoupper($k);
            if (property_exists($class, $property)) {
                if (
                    $property == 'SSL_CERT_PATH'
                    || $property == 'SSL_KEY_PATH'
                ) {
                    $v = $this->getFilePath($v);
                }
                $class::$$property = $v;
            }
        }
        unset($v);
        return [];
    }

    public function loadConfigureOfAliPay()
    {
        if (!$this->configs[self::WITH_ALI_PAY]) {
            throw new Exception('Config for ' . self::WITH_ALI_PAY . ' cannot be empty.');
        }
        return $this->specifyConfig = $this->configs[self::WITH_ALI_PAY];
    }

    public function getConfig()
    {
        if (!$this->specifyConfig) $this->loadConfigure();

        return $this->specifyConfig;
    }

    /**
     * Get file path according to file path alias
     * @param string $alias
     * @return string
     */
    protected function getFilePath($alias)
    {
        // To be compatible with Yii2
        // History issue, this is first designed for Yii2
        if (defined('YII2_PATH')) {
            return \Yii::getAlias($alias);
        }
        return $alias;
    }

}