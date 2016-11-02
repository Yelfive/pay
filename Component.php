<?php

namespace fk\pay;

use fk\pay\config\Platform;
use fk\pay\lib\wechat\Config;
use fk\pay\lib\wechat\JsApi;
use fk\pay\lib\wechat\Pay;
use fk\pay\lib\wechat\Result;
use fk\pay\lib\wechat\TransferData;
use fk\pay\lib\wechat\UnifiedOrderData;
use yii\base\Configurable;

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @method $this setChannel(string $channel)
 *
 * @property string $notifyUrl @see $notifyPath
 * @property string $channel @see $notifyPath
 */
class Component implements Configurable
{

    /**
     * @var Platform
     */
    public $platforms;

    /**
     * @var string
     * As for different platforms,
     * the actual notify_url will need a prefix of [[platform name]],
     * and end with [[.php]]
     * e.g.
     * $notifyPath = 'https://api.alijian.net/notify/';
     * $channel = 'WeChat';
     * $notifyUrl will be `https://api.alijian.net/notify/we-chat.php`
     */
    public $notifyPath;

    public function __construct($config = [])
    {
        if (empty($config['notifyPath'])) throw new Exception('Property notifyPath must be set and must not be empty');
        $this->notifyPath = $config['notifyPath'];
        $this->platforms = new Platform($config['platforms']);
        $this->with($config['channel'] ?? null);
    }

    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new Exception('Trying to get property of unknown');
        }
    }

    /**
     * TODO: unfinished
     * Change the config set in $this->platform
     * @param string $name
     * @param array $arguments
     * @return $this
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        static $properties = [];
        if (!$properties) {
            $reflectionClass = new \ReflectionClass($this);
            $reflectionProperties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($reflectionProperties as $reflectionProperty) {
                $properties[$reflectionProperty->getName()] = true;
            }
        }

        // setXxx
        if (0 === strncmp($name, 'set', 3)) {
            $property = lcfirst(substr($name, 3));
            $this->$property = $arguments[0];
            return $this;
        }
        throw new Exception('Unknown method: ' . $name);
    }

    public function getChannel()
    {
        return $this->platforms->channel;
    }

    public function getNotifyUrl()
    {
        return $this->notifyPath . preg_replace_callback('/([A-Z])/', function ($word) {
            return '-' . strtolower($word[1]);
        }, lcfirst($this->getChannel())) . '.php';
    }

    /**
     * Yii::$app->pay->with('AliPay')->transfer();
     * @param $channel
     * @return $this
     * @throws Exception
     */
    public function with($channel)
    {
        if ($channel && $channel !== $this->channel) $this->platforms->with($channel);
        return $this;
    }

    /**
     * e.g.
     * ```php
     *  $component->with('WeChat')->app('web');
     * ```
     * Means the platform is WeChat, and the configuration is read from `web`
     * @see Platform::app
     * @param string $name
     * @return $this
     */
    public function app($name)
    {
        if ($name && $name != $this->platforms->getAppName()) $this->platforms->app($name);
        return $this;
    }

    /**
     * Unified entry for every payment
     * @param string $orderSn
     * @param float $amount Money
     * @param string $name Goods name
     * @param string $description Goods description
     * @param array $extra Extra params for payment, differs from platforms
     * @return mixed
     * @throws Exception
     */
    public function pay($orderSn, $amount, $name, $description, $extra = [])
    {
        $method = "payWith{$this->channel}";
        if (method_exists($this, $method)) {
            return $this->invoke($method, [$orderSn, $amount, $name, $description, $extra]);
        } else {
            throw new Exception('Channel not supported yet: ' . $this->channel);
        }
    }

    protected function invoke($method, $params)
    {
        $this->platforms->loadConfigure();
        return call_user_func_array([$this, $method], $params);
    }

    /**
     * @param string $orderSn
     * @param string $id User id on Third Part Platform. openid for WeChat
     * @param float $amount
     * @param array $extra
     * @return array
     * @throws Exception
     */
    public function transfer($orderSn, $id, $amount, $extra)
    {
        $method = "transferWith{$this->channel}";

        if (method_exists($this, $method)) {
            $this->platforms->loadConfigure();
            return $this->invoke($method, [$orderSn, $id, $amount, $extra]);
        } else {
            throw new Exception('Channel not supported yet: ' . $this->channel);
        }
    }

    /**
     * @param string $orderSn
     * @param string $id Open id
     * @param float $amount
     * @param array $extra
     * @return array
     * @throws lib\wechat\Exception
     */
    protected function transferWithWeChat($orderSn, $id, $amount, $extra)
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
     * @return string
     * @throws Exception
     */
    protected function payWithWeChat($orderSn, $amount, $name, $description, $extra = [])
    {
        $order = new UnifiedOrderData();

        $order->SetBody($name);
        if (is_array($description)) {
            if (!isset($description['goods_id'])) throw new Exception('goods_id is required by field "detail"');
            if (!isset($description['goods_name'])) throw new Exception('goods_name is required by field "detail"');
            if (!isset($description['goods_num'])) throw new Exception('goods_num is required by field "detail"');
            if (!isset($description['goods_price'])) throw new Exception('goods_price is required by field "detail"');
            $description = json_encode($description, JSON_UNESCAPED_UNICODE);
        }
        $order->SetDetail($description);
        $order->SetOut_trade_no($orderSn);
        $order->SetTotal_fee(round($amount * 100));

        if (!isset($extra['trade_type'])) throw new Exception('Miss required field: "trade_type"');
        $order->SetTrade_type($extra['trade_type']);

        $order->SetNotify_url($this->getNotifyUrl());
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
            case Constant::WX_TRADE_TYPE_JS:
                $model = new JsApi();
                $data = $model->GetJsApiParameters($result);
                break;
            case Constant::WX_TRADE_TYPE_APP:
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

    public function notify($channel, $callback)
    {
        /** @var \fk\pay\notify\NotifyInterface $className */
        $className = "fk\\pay\\notify\\{$channel}Notify";
        if (!class_exists($className)) {
            \Yii::error('Notify class for channel does not exist. channel: ' . $channel);
        }
        $this->platforms->loadConfigure();
        $className::handle($callback);
    }

}