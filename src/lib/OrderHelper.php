<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\lib;

class OrderHelper
{
    public const CATEGORY_DEFAULT = 10;
    public const CATEGORY_REFUND = 11;

    protected static function randomIP()
    {
        return implode('.', array_map(function () {
            return rand(1, 255);
        }, array_fill(0, 4, 0)));
    }

    /**
     * A Serial Number is made of four parts:
     *
     * **$category + $timestamp + $random + $pk**
     *
     * - $category.length = 2~3
     * - $ts.length = 12
     * - $pk.length = 1~11, preceding 0 means ip address
     * - $rand.length = 3
     * @param null|int $pk
     * @param int $category
     * @return string
     */
    public static function generateSN($pk = null, $category = self::CATEGORY_DEFAULT): string
    {
        if (!$pk || !is_numeric($pk)) {
            $ip = $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? static::randomIP());
            $pk = '0' . intval(sprintf(str_repeat('%08b', 4), ...explode('.', $ip)), 2);
        }
        $ts = sprintf('%12d', microtime(true) * 100);
        $rand = mt_rand(100, 999);
        if (!is_int($category)) $category = intval($category);
        if (!$category || $category < 1) {
            $category = 1;
        } else if ($category > 999) {
            $category = 999;
        }
        $sn = "$category$ts$rand$pk";
        return $sn;
    }
}