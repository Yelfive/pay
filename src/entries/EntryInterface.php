<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

interface EntryInterface
{
    /**
     * @param string $orderSn
     * @param float $amount
     * @param string $name Goods name
     * @param string $description
     * @param array $extra
     * @return mixed
     */
    public function pay(string $orderSn, float $amount, string $name, string $description, array $extra = []);

    public function checkSignature(array $data): bool;

    /**
     * Async notify
     * @param callable $callback
     * @return mixed result of notify, success or error, differs from different platform
     */
    public function notify(callable $callback);

    /**
     * @param $order_sn
     * @param $refund_sn
     * @param $total_amount
     * @param null $refund_amount
     * @return bool Whether refund succeeded.
     */
    public function refund($order_sn, $refund_sn, $total_amount, $refund_amount = null): bool;
}