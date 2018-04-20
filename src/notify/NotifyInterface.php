<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay\notify;

use fk\pay\PlatformsConfig;

interface NotifyInterface
{
    /**
     * Method to handle a notify,
     * the parameter `callback` will have an argument of corresponding notify result
     * @param callable $callback
     * @param PlatformsConfig $config
     * @return mixed
     */
    public static function handle(callable $callback, PlatformsConfig $config);
}