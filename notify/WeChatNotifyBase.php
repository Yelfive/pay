<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay\notify;

use fk\pay\lib\wechat\Notify;

class WeChatNotifyBase extends Notify
{

    public $process;

    public function NotifyProcess($data, &$msg)
    {
        $method= $this->process;
        return $method($data, $msg);
    }

}