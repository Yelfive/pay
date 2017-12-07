<?php

namespace fk\pay\notify;
/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */
class WeChatNotify implements NotifyInterface
{

    /**
     * @param callable $callback
     */
    public static function handle($callback)
    {
        $notify = new WeChatNotifyBase();
        $notify->process = $callback;
        $notify->Handle();
    }
}