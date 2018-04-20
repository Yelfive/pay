<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2018-04-20
 */

namespace fk\pay\notify;

use fk\pay\lib\alipay\wap\service\AliPayTradeService;
use fk\pay\PlatformsConfig;

class AliPayNotify implements NotifyInterface
{

    /**
     * Method to handle a notify,
     * the parameter `callback` will have an argument of corresponding notify result
     * @param callable $callback
     * @param PlatformsConfig $config
     * @return mixed
     */
    public static function handle(callable $callback, PlatformsConfig $config)
    {
        $service = new AliPayTradeService($config->getWorkingConfig());
        $service->writeLog(var_export($_POST, true));
        $result = $service->check($arr);
    }
}