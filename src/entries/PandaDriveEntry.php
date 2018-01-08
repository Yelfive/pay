<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-06
 */

namespace fk\pay\entries;

use fk\helpers\ArrayHelper;
use GuzzleHttp\Client;

class PandaDriveEntry extends Entry
{

    public const CHANNEL_WECHAT = 'wx_web_h5';
    public const CHANNEL_ALIPAY = 'alipay_wap';

    public const RESOURCE_PANDA_BANK = 1;

    public const FEE_TYPE_CNY = 1;

    protected const RESULT_CODE_SUCCESS = 0;

    public const TRADE_STATE_SUCCESS = 0;
    public const TRADE_STATE_FAILURE = 1;
    public const TRADE_STATE_PENDING = 3;

    public $response;

    /**
     * 0：退款成功。
     */
    public const REFUND_SUCCESS = 0;
    /**
     * 1：退款失败。
     */
    public const REFUND_FAILED = 1;
    /**
     * 3：退款处理中。
     */
    public const REFUND_PROCESSING = 3;
    /**
     * 4：未确定，需要商户原退款单号重新发起。
     */
    public const REFUND_UNCONFIRMED = 4;
    /**
     * 5：转入代发，退款到银行发现用户的卡作废或者冻结了，导致原路退款银行卡失败，资金回流到商户的现金帐号，需要商户人工干预，通过线下或者平台转账
     */
    public const REFUND_TRANSFER_MANUALLY = 5;

    /**
     * @param string $orderSn
     * @param float $amount
     * @param string $name Goods name
     * @param string $description
     * @param array $extra
     * @return mixed
     */
    public function pay(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
        return $this->insideWeChat() ? $this->payInsideWeChat(...func_get_args()) : $this->payWithH5(...func_get_args());
    }

    protected function insideWeChat()
    {
        return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false;
    }

    protected function payInsideWeChat(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
    }

    protected function payWithH5(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
        $this->required($this->config, ['sh_name', 'key', 'subpartner', 'notify_url']);
        $this->required($extra, ['show_url', 'channel', 'return_url']);

        $data = [
            'sh_name' => $this->config['sh_name'],
            'subpartner' => $this->config['subpartner'],
            // User of your platform
            'userid' => $extra['uid'] ?? 0,
            'resource' => self::RESOURCE_PANDA_BANK,

            // business parameters
            'out_trade_no' => $orderSn,
            'subject' => $name,
            // The url when user decide not to pay and return to previous page with
            'show_url' => $extra['show_url'],
            'body' => $description,
            'total_fee' => $amount,
            'fee_type' => self::FEE_TYPE_CNY,
            'spbill_create_ip' => $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'],
            'notify_url' => $this->notifyUrl,
            'return_url' => $extra['return_url'],
            // @see self::CHANNEL_*
            'trans_channel' => $extra['channel'],
        ];

        $this->sign($data);
        return $this->post('payByH5', $data);
    }

    protected function post($api, $data)
    {
        $client = new Client();
        $uri = "{$this->config['host']}/$api.php";

        $result = $client->request('POST', $uri, [
            'form_params' => $data
        ]);
        return $result->getBody()->getContents();
    }

    /**
     * @param array $data
     * @param callable $callback
     * @return mixed|false False to indicates the failure
     */
    public function notify(array $data, callable $callback)
    {
        if ($this->validate($data)) {
            if (!isset($data['trade_state'])) return false;
            if ($data['trade_state'] == self::TRADE_STATE_PENDING) return false;
            return $callback($data);
        } else {
            return false;
        }
    }

    protected function validate(array $data): bool
    {
        if (!$this->checkSignature($data)) return false;
        if (!isset($data['sh_name']) || $this->config['sh_name'] != $data['sh_name']) return false;
        if (!isset($data['retcode']) && $data['retcode'] != self::RESULT_CODE_SUCCESS) return false;
        return true;
    }

    public function checkSignature(array $data): bool
    {
        $sign = $data['sign'] ?? '';
        if (!$sign) return false;
        unset($data['sign']);
        return $sign === $this->sign($data);
    }

    /**
     * Set and return signature
     * @param array &$data
     * @return string
     */
    protected function sign(array &$data)
    {
        ksort($data);

        $result = '';
        foreach ($data as $k => $v) {
            if (strlen($v) === 0) continue;
            $result .= "$k=$v&";
        }
        $result = rtrim($result, '&');

        return $data['sign'] = md5("{$result}{$this->config['key']}");
    }

    /**
     * @param string $orderSN
     * @param string $orderSNOfThirdParty
     * @param string $refundSN
     * @param int $uid
     * @param float $totalAmount
     * @param null|float $refundAmount default `$totalAmount`
     * @param string $reason
     * @return array|false
     */
    public function refund($orderSN, $orderSNOfThirdParty, $refundSN, $uid, $totalAmount, $refundAmount = null, $reason)
    {
        $requiredConfig = ['sh_name', 'subpartner'];
        $this->required($this->config, $requiredConfig);
        $data = ArrayHelper::only($this->config, $requiredConfig)
            + [
                'resource' => self::RESOURCE_PANDA_BANK,
                'out_trade_no' => $orderSN,
                'transaction_id' => $orderSNOfThirdParty,
                'out_refund_no' => $refundSN,
                'refund_fee' => $refundAmount ?? $totalAmount,
                'userid' => $uid,
                'refund_reason' => $reason,
                'total_fee' => $totalAmount,
            ];
        $this->sign($data);
        $body = $this->post('refund', $data);
        $this->response = $result = json_decode($body, true);
        if ($this->validate($result)) {
            return $result;
        } else {
            return false;
        }
    }

    public function subpartner($id)
    {
        $this->config['subpartner'] = $id;
        return $this;
    }
}