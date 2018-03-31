<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-07
 */

namespace fk\pay;

use fk\helpers\{
    Dumper, SingletonTrait
};
use fk\pay\config\ConfigContainer;
use fk\pay\entries\{
    AliPayEntry, Entry, PandaDriveEntry, WeChatEntry
};

/**
 * @method PandaDriveEntry usePandaDrive(string $scenario = null)
 * @method WeChatEntry useWeChat(string $scenario = null)
 * @method AliPayEntry useAliPay(string $scenario = null)
 */
class PayUniform
{

    use SingletonTrait;

    /**
     * @var PlatformsConfig
     */
    protected $platforms;

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
     *      ],
     *      AliPay: [
     *      ]
     *  ]
     * ```
     * @var string
     */
    protected $app;

    public function __construct(array $config)
    {
        $this->platforms = new PlatformsConfig($config);
    }

    public function useScenario($scenarioName)
    {
        $this->platforms->useScenario($scenarioName);
        return $this;
    }

    /**
     * Invoked when calling `useXYZ()`
     * @param string $name
     * @param array $arguments
     * @return Entry
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (strncmp($name, 'use', 3) === 0) {
            $platform = substr($name, 3);
            $this->platforms->usePlatform($platform);
            /** @var Entry $entry */
            $entry = $this->loadEntry($arguments[0] ?? null);
            return $entry;
        }
        $params = substr(Dumper::dump($arguments, true), 1, -1);

        throw new Exception("Calling to unknown method: $name($params)");
    }

    /**
     * @param $scenario
     * @return Entry
     * @throws Exception
     */
    protected function loadEntry($scenario)
    {
        if ($scenario) $this->platforms->useScenario($scenario);

        $platformName = $this->platforms->getChosenPlatform();
        $entryClass = "\\fk\pay\\entries\\{$platformName}Entry";
        if (!class_exists($entryClass)) {
            throw new Exception("Platform $platformName not supported");
        }
        /** @var Entry $entry */
        $entry = new $entryClass($this->platforms);

        return $entry;
    }

    /**
     * @see PlatformsConfig::getWorkingConfig()
     * @param null|string $dotKey
     * @return array|mixed|string
     * @throws Exception
     */
    public function getWorkingConfig($dotKey = null)
    {
        return $this->platforms->getWorkingConfig($dotKey);
    }
}