<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay;

use fk\fluent\FluentArray;

class Constant
{
    public const WECHAT_TRADE_TYPE_JS = 'JSAPI';
    public const WECHAT_TRADE_TYPE_NATIVE = 'NATIVE';
    public const WECHAT_TRADE_TYPE_APP = 'APP';

    public const PLATFORM_WECHAT = 'WeChat';
    public const PLATFORM_ALIPAY = 'AliPay';

    /**
     * @param null $prefix
     * @return FluentArray
     * @throws \ReflectionException
     */
    public static function all($prefix = null)
    {
        $rc = new \ReflectionClass(__CLASS__);
        $constants = [];
        foreach ($rc->getConstants() as $key => $value) {
            if (!$prefix || strncmp($prefix, $key, strlen($prefix)) === 0) {
                $constants[$key] = $value;
            }
        }
        return FluentArray::build($constants);
    }
}