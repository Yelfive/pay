<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\Constant;
use fk\pay\Exception;
use fk\pay\lib\WeChat\Config;
use fk\pay\lib\WeChat\JsApi;
use fk\pay\lib\WeChat\Pay;
use fk\pay\lib\WeChat\Refund;
use fk\pay\lib\WeChat\Result;
use fk\pay\lib\WeChat\TransferData;
use fk\pay\lib\WeChat\UnifiedOrderData;

class WeChatEntry extends Entry
{

    /**
     * @link https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1
     * @link https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1
     * @param string $orderSn
     * @param float $amount Unit: Yuan
     * @param string $name
     * @param string|array $description
     * @param array $extra This should be identical to WeChat unified pay params,
     * In the form of `param => value`
     * e.g.<pre>
     * ```php
     *  [
     *      'trade_type' => 'JSAPI', // required, JSAPI, NATIVE, APP
     *      'time_start' => 1412345678, // optional
     *      'expires_in_seconds' => 600, // optional
     *      'redirect_url',
     *  ]
     * ```
     * </pre>
     * @return array
     * @throws Exception
     * @throws \fk\pay\lib\WeChat\Exception
     */
    public function pay(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
        $order = new UnifiedOrderData();

        if (is_array($description) && $this->required($description, ['goods_id', 'goods_name', 'goods_num', 'goods_price'])) {
            $description = json_encode($description, JSON_UNESCAPED_UNICODE);
        }
        $order->SetBody($name);
        $order->SetDetail($description);
        $order->SetOut_trade_no($orderSn);
        $order->SetTotal_fee(round($amount * 100));
        if (isset($extra['expires_in_seconds'])) {
            $order->SetTime_expire(date('YmdHis', time() + (int)$extra['expires_in_seconds']));
            unset($extra['expires_in_seconds']);
        }

        $this->required($extra, ['trade_type'], '"{attribute}" is required in `$extra`');

        $order->SetTrade_type($extra['trade_type']);
        unset($extra['trade_type']);

        $order->SetNotify_url($this->config->getWorkingConfig('notify_url'));
        // Set extra params
        foreach ($extra as $k => &$v) {
            $method = 'Set' . ucfirst($k);
            if (method_exists($order, $method)) $order->$method($v);
        }

        $result = Pay::unifiedOrder($order);
        if ($result['return_code'] === 'FAIL') {
            throw new Exception($result['return_msg']);
        } else if ($result['result_code'] === 'FAIL') {
            throw new Exception("{$result['err_code_des']}({$result['err_code']})");
        }

        switch ($order->GetTrade_type()) {
            case Constant::WECHAT_TRADE_TYPE_JS:
                $model = new JsApi();
                $data = $model->GetJsApiParameters($result);
                break;
            case Constant::WECHAT_TRADE_TYPE_APP:
                $data = [
                    'appid' => Config::$APP_ID,
                    'partnerid' => Config::$MCH_ID,
                    'prepayid' => $result['prepay_id'],
                    'package' => 'Sign=WXPay',
                    'noncestr' => Pay::getNonceStr(),
                    'timestamp' => $_SERVER['REQUEST_TIME'],
                ];
                $wx = new Result();
                $wx->FromArray($data);
                $data['sign'] = $wx->MakeSign();
                // WeChat need `package` as param for payment API,
                // however, package is a keyword in Android
                $data['packageValue'] = $data['package'];
                unset($data['package']);
                break;
            case Constant::WECHAT_TRADE_TYPE_H5:
                $location = $result['mweb_url'];
                if (!empty($extra['redirect_url'])) $location .= (strpos($location, '?') ? '&' : '?') . 'redirect_url=' . urlencode($extra['redirect_url']);
                $data = [
                    'redirect' => $location
                ];
                break;
            case Constant::WECHAT_TRADE_TYPE_NATIVE:
                $data = [
                    'pay_uri' => $result['code_url']
                ];
                break;
            default:
                $data = [];
        }

        return $data;
    }

    /**
     * Enterprise transfer to an individual user
     * @param string $orderSn
     * @param string $id Third party id
     * @param float $amount
     * @param array $extra
     * @return array
     * @throws \fk\pay\lib\WeChat\Exception
     */
    public function transfer($orderSn, $id, $amount, array $extra)
    {
        $transfer = new TransferData();
        $transfer->setPartner_trade_no($orderSn)
            ->setOpenid($id)
            ->setAmount(round($amount * 100));
        foreach ($extra as $k => &$v) {
            if (TransferData::paramExists($k)) $transfer->{'set' . ucfirst($k)}($v);
        }
        return Pay::transfer($transfer);
    }

    public function checkSignature(array $data): bool
    {
        return true;
    }

    /**
     * @throws Exception
     */
    protected function setConfig()
    {
        foreach ($this->config->getWorkingConfig() as $k => $v) {
            $property = strtoupper($k);
            if (property_exists(Config::class, $property)) {
                Config::$$property = $v;
            }
        }
    }

    /**
     * @param string $order_sn
     * @param string $refund_sn
     * @param float $total_amount
     * @param null|float $refund_amount
     * @return bool Whether refund succeeded.
     */
    public function refund($order_sn, $refund_sn, $total_amount, $refund_amount = null): bool
    {
        $total_fee = round($total_amount * 100);
        $refund_fee = round(100 * ($refund_amount ?: $total_amount));

        $refund = new Refund();
        $refund->SetOut_trade_no($order_sn);
        $refund->SetOut_refund_no($refund_sn);
        $refund->SetTotal_fee($total_fee);
        $refund->SetRefund_fee($refund_fee);

        $this->response = Pay::refund($refund);

        return $this->response['return_code'] === 'SUCCESS';
    }
}