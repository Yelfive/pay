<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2018-07-04
 */

namespace fk\pay\lib\AliPay\result;

/**
 * Class TransferResult
 * @property string $code
 * @property string $msg
 * @property string $sub_code
 * @property string $out_biz_no
 * @property string $pay_date Only when success. e.g. 2018-07-04 10:47:20
 * @property string $order_id Order serial number of AliPay, this may(or not) returned when transfer failed.
 */
class TransferResult extends ResultContract
{
    protected function setAlipayFundTransToAccountTransferResponse($data)
    {
        $this->configure($data);
    }
}