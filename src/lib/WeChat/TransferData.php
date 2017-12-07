<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay\lib\wechat;

/**
 *
 * @method TransferData setMch_appid(string $value)
 * @method TransferData setMchid(string $value)
 *
 * @method TransferData setPartner_trade_no(string $value)
 * @method TransferData setOpenid(string $value)
 * @method TransferData setCheck_name(string $value)
 * @method TransferData setRe_user_name(string $value)
 * @method TransferData setAmount(int $value)
 * @method TransferData setDesc(string $value)
 * @method TransferData setSpbill_create_ip(string $value)
 * @method TransferData setDevice_info(string $value)
 * @method TransferData setNonce_str(string $value)
 *
 * @method string getPartner_trade_no()
 * @method string getOpenid()
 * @method string getCheck_name()
 * @method string getRe_user_name()
 * @method string getAmount(int $value)
 * @method string getDesc()
 * @method string getSpbill_create_ip()
 * @method string getDevice_info()
 */
class TransferData extends BaseData
{

    public static $params = [
        'mch_appid',
        'mchid',
        'nonce_str',
        'sign',

        // ======== Required ========= //
        'partner_trade_no', //string 商户订单号，需保持唯一性
        'openid',   // string 商户appid下，某用户的openid

        /*
         * 校验用户姓名选项
         * - NO_CHECK：不校验真实姓名
         * - FORCE_CHECK：强校验真实姓名（未实名认证的用户会校验失败，无法转账）
         * - OPTION_CHECK：针对已实名认证的用户才校验真实姓名（未实名认证用户不校验，可以转账成功）
         */
        'check_name',
        /*
         * 收款用户真实姓名。
         * 如果 check_name 设置为
         * FORCE_CHECK 或 OPTION_CHECK
         * 则必填用户真实姓名
         * @var string
         */
        're_user_name',
        /*
         * @var int 企业付款金额，单位为分
         */
        'amount',
        /*
         * @var string 企业付款操作说明信息
         */
        'desc',
        /*
         * @var string 调用接口的机器Ip地址
         */
        'spbill_create_ip',
        // ======== Optional ======== //
        /*
         * @var string 微信支付分配的终端设备号
         */
        'device_info',
    ];

    /**
     * Set values to params of enterprise transfer API
     * Example:
     * ```php
     *      $transfer->setAmount(100)
     * ```
     * means
     * ```php
     *      $transfer->values['amount'] = 100;
     * ```
     * @param string $name
     * @param array $arguments
     * @return null
     * @throws Exception
     */
    public function __call($name, $arguments)
    {

        if (strncmp($name, 'set', 3) === 0) {
            $param = strtolower(substr($name, 3));
            if (static::paramExists($param)) {
                $this->values[$param] = $arguments[0];
                return $this;
            }
        } else if (strncmp($name, 'get', 3) === 0) {
            $param = strtolower(substr($name, 3));
            if (static::paramExists($param)) {
                return $this->values[$param];
            }
        }
        throw new Exception('Trying to call undefined method: ' . $name);
    }

    public static function paramExists($name)
    {
        return in_array($name, static::$params);
    }

    public function valueExists($param)
    {
        return !empty($this->values[$param]);
    }
}