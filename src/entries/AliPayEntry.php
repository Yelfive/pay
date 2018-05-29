<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\lib\AliPay\wap\builders\AliPayTradeRefundContentBuilder;
use fk\pay\lib\AliPay\wap\service\AliPayTradeService;
use fk\pay\lib\AliPay\wap\builders\AliPayTradeWapPayContentBuilder;

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

    public $response;

    protected function setConfig()
    {
        defined('AOP_SDK_WORK_DIR') or define('AOP_SDK_WORK_DIR', $this->config->getWorkingConfig('log_path') ?? dirname(__DIR__) . '/lib/AliPay/logs');
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
     * @param array $extra <pre>
     *  [
     *      'expires_in_seconds' => 600, // optional
     *      'return_url' => '', required
     *  ]
     * </pre>
     *
     * @return mixed
     * @throws \Exception
     * @throws \fk\pay\Exception
     */
    public function pay(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
        $this->required($extra, ['return_url']);
        $builder = new AliPayTradeWapPayContentBuilder();
        $builder->setOutTradeNo($orderSn);
        $builder->setTotalAmount($amount);
        $builder->setBody($description);
        $builder->setSubject($name);
        if (isset($extra['expires_in_seconds'])) {
            $builder->setTimeExpress(sprintf('%dm', $extra['expires_in_seconds'] / 60));
        }

        $service = new AliPayTradeService($this->config->getWorkingConfig());
        $this->response = $service->wapPay($builder, $extra['return_url'], $this->config->getWorkingConfig('notify_url'));

        return $this->response;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function checkSignature(array $data): bool
    {
        $valid = (new AliPayTradeService($this->config->getWorkingConfig()))->check($data);
        return is_bool($valid) ? $valid : false;
    }

    /**
     * @param string $order_sn
     * @param string $refund_sn
     * @param string $total_amount
     * @param null $refund_amount
     * @return bool Whether refund succeeded.
     */
    public function refund($order_sn, $refund_sn, $total_amount, $refund_amount = null): bool
    {
        $request = new AlipayTradeRefundContentBuilder();
        $request->setOutTradeNo($order_sn);
        $request->setRefundAmount($total_amount);
        $request->setRefundReason('No reason');
        $request->setOutRequestNo($refund_sn);

        $service = new AlipayTradeService($this->config->getWorkingConfig());
        $this->response = $service->Refund($request);
        return $this->response->code == '10000';
    }
}