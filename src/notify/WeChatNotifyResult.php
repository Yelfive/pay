<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2018-03-31
 */

namespace fk\pay\notify;

class WeChatNotifyResult extends ResultContract
{

    public const RESULT_CODE_SUCCESS = 'SUCCESS';
    public const RESULT_CODE_FAILURE = 'FAIL';

    /**
     *  [
     *      [type => '', id => '', fee => ''],
     *      [type => '', id => '', fee => ''],
     *  ]
     * @var array
     */
    public $coupons;

    /**
     * @var string
     */
    public $return_code;

    /**
     * @var string
     */
    public $return_msg;

    /**
     * @var string 公众账号ID
     */
    public $appid;

    /**
     * @var string 商户号
     */
    public $mch_id;

    /**
     * @var string 设备号
     */
    public $device_info;

    /**
     * @var string 随机字符串
     */
    public $nonce_str;

    /**
     * @var string 签名
     */
    public $sign;

    /**
     * @var string 签名类型
     */
    public $sign_type;

    /**
     * @var string 业务结果
     */
    public $result_code;

    /**
     * @var string 错误代码
     */
    public $err_code;

    /**
     * @var string 错误代码描述
     */
    public $err_code_des;

    /**
     * @var string 用户标识
     */
    public $openid;

    /**
     * @var string 是否关注公众账号
     */
    public $is_subscribe;

    /**
     * @var string 交易类型
     */
    public $trade_type;

    /**
     * @var string 付款银行
     */
    public $bank_type;

    /**
     * @var string 订单金额
     */
    public $total_fee;

    /**
     * @var string 应结订单金额
     */
    public $settlement_total_fee;

    /**
     * @var string 货币种类
     */
    public $fee_type;

    /**
     * @var string 现金支付金额
     */
    public $cash_fee;

    /**
     * @var string 现金支付货币类型
     */
    public $cash_fee_type;

    /**
     * @var string 总代金券金额
     */
    public $coupon_fee;

    /**
     * @var string 代金券使用数量
     */
    public $coupon_count;

    /**
     * @var string 微信支付订单号
     */
    public $transaction_id;

    /**
     * @var string 商户订单号
     */
    public $out_trade_no;

    /**
     * @var string 商家数据包
     */
    public $attach;

    /**
     * @var string 支付完成时间
     */
    public $time_end;

    public function __construct($data)
    {
        parent::__construct($data);
        for ($i = 0; ; $i++) {
            $key = "coupon_type_$i";
            if (empty($data[$key])) break;
            $this->coupons[$i] = [
                'type' => $data["coupon_type_$i"],
                'id' => $data["coupon_id_$i"],
                'fee' => $data["coupon_fee_$i"],
            ];
        }
    }

}