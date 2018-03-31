<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay\notify;

interface NotifyInterface
{
    /**
     * Method to handle a notify,
     * the parameter `callback` will have an argument of corresponding notify result
     * @param callable $callback
     * @return mixed
     */
    public static function handle(callable $callback);
}