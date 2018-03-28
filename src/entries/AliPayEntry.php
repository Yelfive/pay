<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\lib\alipay\wap\service\AliPayTradeService;
use fk\pay\lib\alipay\wap\builders\AliPayTradeWapPayContentBuilder;
use fk\pay\PlatformsConfig;

class AliPayEntry extends Entry
{

    /**
     * 交易创建，等待买家付款
     */
    const TRADE_STATUS_WAIT_PAYING = 'WAIT_BUYER_PAY';
    /**
     * 交易结束，不可退款
     */
    const TRADE_STATUS_FINISHED = 'TRADE_FINISHED';
    /**
     * 交易支付成功
     */
    const TRADE_STATUS_SUCCESS = 'TRADE_SUCCESS';
    /**
     * 未付款交易超时关闭，或支付完成后全额退款
     */
    const TRADE_STATUS_CLOSED = 'TRADE_CLOSED';

    const NOTIFY_RESULT_SUCCESS = 'success';
    const NOTIFY_RESULT_FAILED = 'failed';

    protected function setConfig()
    {
        defined('AOP_SDK_WORK_DIR') or define('AOP_SDK_WORK_DIR', $this->config['logPath'] ?? dirname(__DIR__) . '/lib/alipay');
    }

    public static function getStatuses()
    {
        return [
            self::TRADE_STATUS_CLOSED, self::TRADE_STATUS_CLOSED,
            self::TRADE_STATUS_SUCCESS, self::TRADE_STATUS_WAIT_PAYING
        ];
    }

    /**
     * @param string $orderSn
     * @param float $amount
     * @param string $name
     * @param string $description
     * @param array $extra
     * @return mixed
     * @throws \Exception
     * @throws \fk\pay\Exception
     */
    public function pay(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
        $builder = new AliPayTradeWapPayContentBuilder();
        $builder->setOutTradeNo($orderSn);
        $builder->setTotalAmount($amount);
        $builder->setBody($description);
        $builder->setSubject($name);
        $builder->setTimeExpress($extra['time_express'] ?? '1d');

        $response = new AliPayTradeService($this->config->getWorkingConfig());
        $result = $response->wapPay($builder, $this->config->getWorkingConfig('return_url'), $this->config->getWorkingConfig('notify_url'));

        return $result;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function checkSignature(array $data): bool
    {
        $valid = (new AliPayTradeService($this->config))->check($data);
        return is_bool($valid) ? $valid : false;
    }
}