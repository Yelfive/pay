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
     * @return boolean Whether notify success handled
     */
    public function notify(callable $callback): bool;
}