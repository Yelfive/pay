<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-23
 */

namespace fk\pay\entries;

use fk\pay\Constant;
use fk\pay\Exception;
use fk\pay\lib\wechat\Config;
use fk\pay\lib\wechat\JsApi;
use fk\pay\lib\wechat\Pay;
use fk\pay\lib\wechat\Result;
use fk\pay\lib\wechat\TransferData;
use fk\pay\lib\wechat\UnifiedOrderData;

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
     * e.g.
     * ```php
     *  [
     *      'trade_type' => 'JSAPI', // required, JSAPI, NATIVE, APP
     *      'time_start' => 1412345678, // optional
     *  ]
     * ```
     * @return array
     * @throws Exception
     * @throws \fk\pay\lib\wechat\Exception
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

        $this->required($extra, ['trade_type'], '"{attribute}" is required in `$extra`');

        $order->SetTrade_type($extra['trade_type']);

        $order->SetNotify_url($this->config['notifyUrl']);
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
     * @throws \fk\pay\lib\wechat\Exception
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
}