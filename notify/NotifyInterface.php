<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay\notify;

interface NotifyInterface
{
    public static function handle($callback);
}