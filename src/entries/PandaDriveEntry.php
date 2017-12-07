<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-06
 */

namespace fk\pay\entries;

class PandaDriveEntry extends Entry
{

    public const CHANNEL_WECHAT = 'wx_web_h5';
    public const CHANNEL_ALIPAY = 'alipay_wap';

    public const RESOURCE_PANDA_BANK = 1;

    public const FEE_TYPE_CNY = 1;


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
        return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== -1;
    }

    protected function payInsideWeChat(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
    }

    protected function payWithH5(string $orderSn, float $amount, string $name, string $description, array $extra = [])
    {
        $this->required($this->config, ['sh_name', 'key', 'subpartner']);
        $this->required($extra, ['show_url', 'channel']);
        $data = [
            'sh_name' => $this->config['sh_name'],
            'subpartner' => $this->config['subpartner'],
            // User of your platform
            'userid ' => $extra['uid'],
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
            'return_url' => $this->returnUrl,
            // @see self::CHANNEL_*
            'trans_channel' => $extra['channel'],
        ];

        $this->sign($data);
    }

    public function refund()
    {
    }

    public function checkSignature(array $data): bool
    {
        $sign = $data['sign'];
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
            if (empty($v)) continue;
            $result .= "$k=$v";
        }

        return $data['sign'] = md5("{$result}{$this->config['key']}");
    }
}