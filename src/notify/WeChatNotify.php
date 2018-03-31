<?php

namespace fk\pay\notify;
/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */
class WeChatNotify implements NotifyInterface
{

    /**
     * @param callable $callback
     * @return string
     */
    public static function handle($callback)
    {
        $notify = new WeChatNotifyBase();
        $notify->process = $callback;
        ob_start();
        $notify->Handle();
        return ob_get_clean();
    }
}