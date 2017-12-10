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

    public static function generateSN($pk = null, $category = 10): string
    {
        if (is_numeric($pk)) {
            $pk = sprintf('%012d', $pk);
        } else {
            $ip = $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $pk = sprintf('%03d%03d%03d%03d', ...explode('.', $ip));
        }
        $ts = sprintf('%13d', microtime(true) * 1000);
        $rand = mt_rand(100, 999);
        if (!is_int($category)) $category = intval($category);
        if (!$category || $category < 1) {
            $category = 1;
        } else if ($category > 999) {
            $category = 999;
        }
        /*
         * category, pk, timestamp, random number
         * $pk.length = 12
         * $ts.length = 13
         * $rand.length = 4
         * $category.length = 3
         */
        $SN = "$category$pk$ts$rand";
        return $SN;
    }
}