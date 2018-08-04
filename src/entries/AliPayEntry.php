<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\Constant;
use fk\pay\lib\AliPay\aop\AopClient;
use fk\pay\lib\AliPay\aop\request\AlipayFundTransToaccountTransferRequest;
use fk\pay\lib\AliPay\result\TransferResult;
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

    /**
     * @var mixed
     */
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
     *      'return_url' => '', // required
     *      'trade_type' => Constant::ALIPAY_TRADE_TYPE_QR, // required when for QRCode paying
     *  ]
     * </pre>
     *
     * @return array
     * @throws \Exception
     * @throws \fk\pay\Exception
     */
    public function pay(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
        // AliPay needs a encode
        $description = urlencode($description);
        $builder = new AliPayTradeWapPayContentBuilder();
        $builder->setOutTradeNo($orderSn);
        $builder->setTotalAmount($amount);
        $builder->setBody($description);
        $builder->setSubject($name);
        if (isset($extra['expires_in_seconds'])) {
            $builder->setTimeExpress(sprintf('%dm', $extra['expires_in_seconds'] / 60));
        }

        $service = new AliPayTradeService($this->config->getWorkingConfig());

        $notifyUri = $this->config->getWorkingConfig('notify_url');
        if (isset($extra['trade_type']) && $extra['trade_type'] === Constant::ALIPAY_TRADE_TYPE_QR) {
            $this->response = $service->preCreate($builder, $notifyUri);
            $result = [
                'pay_uri' => $this->response->alipay_trade_precreate_response->qr_code,
            ];
        } else {
            $this->required($extra, ['return_url']);
            $result = $this->response = $service->wapPay($builder, $extra['return_url'], $notifyUri);
        }

        return $result;
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

    public const TRANSFER_PAYEE_TYPE_UID = 'ALIPAY_USERID';
    public const TRANSFER_PAYEE_TYPE_LID = 'ALIPAY_LOGONID';

    /**
     * @param string $payee_account
     * @param string $transfer_sn
     * @param float $amount CNY
     * @param string $remark [optional]
     * @param string $payee_real_name [optional]
     * @param string $payee_type [optional] ALIPAY_USERID or ALIPAY_LOGONID
     * @param string $payer_show_name [optional]
     * @return TransferResult
     */
    public function transfer($payee_account, $transfer_sn, $amount, $remark = null, $payee_real_name = null, $payee_type = self::TRANSFER_PAYEE_TYPE_LID, $payer_show_name = null)
    {
        $aop = new AopClient();
        $aop->gatewayUrl = $this->config('gatewayUrl');
        $aop->appId = $this->config('app_id');
        $aop->rsaPrivateKey = $this->config('merchant_private_key');
        $aop->alipayrsaPublicKey = $this->config('alipay_public_key');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new AlipayFundTransToaccountTransferRequest();
        $request->setBizContent(json_encode(array_filter([
            'out_biz_no' => $transfer_sn,
            'payee_type' => $payee_type,
            'payee_account' => $payee_account,
            'amount' => round($amount, 2),
            'payer_show_name' => $payer_show_name,
            'payee_real_name' => $payee_real_name,
            'remark' => $remark,
        ], function ($v) {
            return $v !== null;
        }), JSON_UNESCAPED_UNICODE));
        $result = $aop->execute($request);
        return new TransferResult($result);
    }

    protected function config($name)
    {
        return $this->config->getWorkingConfig($name);
    }
}