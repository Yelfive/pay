<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\lib;

class OrderHelper
{
    const CATEGORY_DEFAULT = 10;

    public static function generateSN($pk = null, $category = 10): string
    {
        if (is_numeric($pk)) {
            $pk = sprintf('%012d', $pk);
        } else {
            $ip = $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $pk = sprintf('%03d%03d%03d%03d', ...explode('.', $ip));
        }
        $ts = sprintf('%14d', microtime(true) * 10000);
        $rand = mt_rand(1000, 9999);
        if (!is_int($category)) $category = intval($category);
        if (!$category) $category = 10;
        /*
         * category, pk, timestamp, random number
         * $pk.length = 12
         * $ts.length = 14
         * $rand.length = 4
         * $category.length = 2
         */
        $SN = "$category$pk$ts$rand";
        return $SN;
    }
}