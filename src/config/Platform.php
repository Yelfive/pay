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
    const WITH_PANDA_DRIVE = 'PandaDrive';

    /**
     * All the configure data for all payments
     * @var array Configure array
     */
    public $configs;

    /**
     * Payment specified configure
     * @var array
     */
    public $specifyConfig;

    /**
     * @var string The platform to pay with
     */
    protected $with;

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * Set payment platform
     * @param string|null $platform
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
     * @throws Exception
     */
    public function loadConfigure()
    {
        $platform = $this->with;
        if ($platform == null) throw new Exception('Miss required parameter: platform. You should call `with` to set that');
        if (empty($this->configs[$platform])) throw new Exception("No configuration for such platform: $platform");

        return $this->configs[$platform];
    }

    public function getAppName()
    {
        return $this->_app;
    }

//    protected function loadConfigureOfWeChat($configure)
//    {
//        $class = '\fk\pay\lib\\' . strtolower($this->with) . '\Config';
//        if ($this->_app) {
//            $weChatApp = &$this->_app;
//        } else {
//            $weChatApp = $this->getClient() === 'Web' ? 'web' : 'app';
//        }
//
//        if (empty($configure[$weChatApp])) throw new Exception('Configure for ' . $class . ' is required, while empty given.');
//
//        foreach ($configure[$weChatApp] as $k => &$v) {
//            $property = strtoupper($k);
//            if (property_exists($class, $property)) {
//                $class::$$property = $v;
//            }
//        }
//        unset($v);
//    }

//    public function loadConfigureOfAliPay()
//    {
//        if (!$this->configs[self::WITH_ALI_PAY]) {
//            throw new Exception('Config for ' . self::WITH_ALI_PAY . ' cannot be empty.');
//        }
//        return $this->specifyConfig = $this->configs[self::WITH_ALI_PAY];
//    }

//    public function getConfig()
//    {
//        if (!$this->specifyConfig) $this->loadConfigure();
//
//        return $this->specifyConfig;
//    }

//    /**
//     * Get file path according to file path alias
//     * @param string $alias
//     * @return string
//     */
//    protected function getFilePath($alias)
//    {
//        // To be compatible with Yii2
//        // History issue, this is first designed for Yii2
//        if (defined('YII2_PATH')) {
//            return \Yii::getAlias($alias);
//        }
//        return $alias;
//    }

}