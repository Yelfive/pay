<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 18-3-28
 */

namespace fk\pay;

use fk\helpers\ArrayHelper;

class PlatformsConfig
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $chosenScenario;

    /**
     * @var string
     */
    protected $chosenPlatformName;

    public function __construct(array $platforms)
    {
        $this->config = $platforms;
    }

    public function usePlatform($platformName)
    {
        $this->chosenPlatformName = $platformName;
        return $this;
    }

    /**
     * Get working config for specified platform and chosen scenario.
     * If `$dotKey` is given, this method will return
     * specified config of current working config with dot syntax
     * Such as `app_id` for
     * ```
     *  'WeChat' => [
     *      'scenario' => [
     *          'app_id' => 'wx_app_id'
     *      ]
     *  ]
     * ```
     *
     * **Notice** above that keys `WeChat` and `scenario` is set
     * by calling `usePlatform()` and `useScenario()` respectively
     * @see usePlatform
     * @see useScenario
     * @param null|string $dotKey
     * @return array|string|mixed
     * @throws Exception
     */
    public function getWorkingConfig($dotKey = null)
    {
        $config = $this->config[$this->getChosenPlatform()][$this->getChosenScenario()] ?? null;
        if ($config === null) {
            throw new Exception("Cannot get currently working config, no such config set for platforms.{$this->getChosenPlatform()}.{$this->getChosenScenario()}");
        }
        if ($dotKey === null) return $config;

        return ArrayHelper::get($config, $dotKey, '');
    }

    public function useScenario($scenarioName)
    {
        $this->chosenScenario = $scenarioName;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getChosenPlatform()
    {
        if ($this->chosenPlatformName) {
            return $this->chosenPlatformName;
        }

        throw new Exception('Platform not chosen.');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getChosenScenario()
    {
        if ($this->chosenScenario) return $this->chosenScenario;

        throw new Exception('Scenario not chosen');
    }

}