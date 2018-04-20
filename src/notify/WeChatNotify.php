<?php

namespace fk\pay\notify;

use fk\pay\PlatformsConfig;

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */
class WeChatNotify implements NotifyInterface
{

    /**
     * @param callable $callback
     * @return string
     */
    public static function handle(callable $callback, PlatformsConfig $config)
    {
        $notify = new WeChatNotifyBase();
        $notify->process = $callback;
        ob_start();
        $notify->Handle();
        return ob_get_clean();
    }
}