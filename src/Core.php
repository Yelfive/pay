<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-07
 */

namespace fk\pay;

use fk\helpers\{
    Dumper, SingletonTrait
};
use fk\pay\config\Container;
use fk\pay\entries\{
    AliPayEntry, Entry, PandaDriveEntry, WeChatEntry
};

/**
 * @method PandaDriveEntry withPandaDrive(string $app = null)
 * @method WeChatEntry withWeChat(string $app = null)
 * @method AliPayEntry withAliPay(string $app = null)
 */
class Core
{

    use SingletonTrait;

    /**
     * @var Container
     */
    protected $config;

    /**
     * $pay->with('WeChat')->app('web')
     *
     * ```
     *  [
     *      platforms: [
     *          WeChat: [
     *              web: [
     *                  // config for web payment
     *              ]
     *          ]
     *      ]
     *  ]
     * ```
     * @var string
     */
    protected $app;

    public function __construct(array $config)
    {
        $this->config = new Container($config);
    }

    public function app($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * Invoked when calling `withXYZ()`
     * @param string $name
     * @param array $arguments
     * @return Entry
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (strncmp($name, 'with', 4) === 0) {
            $platform = substr($name, 4);
            $this->config->with($platform);
            $entry = $this->loadEntry();
            if (!empty($arguments[0])) $entry->app($arguments[0]);
            return $entry;
        }
        $params = substr(Dumper::dump($arguments, true), 1, -1);

        throw new Exception("Calling to unknown method: $name($params)");
    }

    /**
     * @return Entry
     * @throws Exception
     */
    protected function loadEntry()
    {
        $platform = $this->config->with();
        $entryClass = "\\fk\pay\\entries\\{$platform}Entry";
        if (!class_exists($entryClass)) {
            throw new Exception("Platform $platform not supported");
        }
        /** @var Entry $entry */
        $entry = new $entryClass();
        $entry->app($this->app);
        $entry->setConfig($this->config->get());

        return $entry;
    }

    public function config()
    {
        return $this->config;
    }

}