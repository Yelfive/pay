<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay\notify;

use fk\pay\lib\WeChat\Notify;

class WeChatNotifyBase extends Notify
{

    public $process;

    public function NotifyProcess($data, &$msg)
    {
        $result = new WeChatNotifyResult($data);
        return call_user_func($this->process, $result, $msg);
    }

}