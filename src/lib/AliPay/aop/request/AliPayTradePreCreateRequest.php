<?php

/**
 * Maintained by Felix Huang <yelfivehuang@gmail.com>
 */

namespace fk\pay\lib\AliPay\aop\request;

/**
 * ALIPAY API: alipay.trade.precreate request
 *
 * @author auto create
 * @since 1.0, 2016-12-08 00:40:23
 */
class AliPayTradePreCreateRequest
{
    /**
     * 收银员通过收银台或商户后台调用支付宝接口，生成二维码后，展示给伤脑筋户，由用户扫描二维码完成订单支付。
     * 修改路由策略到R
     **/
    protected $bizContent;

    protected $apiParas = array();
    protected $terminalType;
    protected $terminalInfo;
    protected $prodCode;
    protected $apiVersion = "1.0";
    protected $notifyUrl;
    protected $returnUrl;
    protected $needEncrypt = false;

    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
        $this->apiParas["biz_content"] = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }

    public function getApiMethodName()
    {
        return "alipay.trade.precreate";
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function getTerminalType()
    {
        return $this->terminalType;
    }

    public function setTerminalType($terminalType)
    {
        $this->terminalType = $terminalType;
    }

    public function getTerminalInfo()
    {
        return $this->terminalInfo;
    }

    public function setTerminalInfo($terminalInfo)
    {
        $this->terminalInfo = $terminalInfo;
    }

    public function getProdCode()
    {
        return $this->prodCode;
    }

    public function setProdCode($prodCode)
    {
        $this->prodCode = $prodCode;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setNeedEncrypt($needEncrypt)
    {

        $this->needEncrypt = $needEncrypt;

    }

    public function getNeedEncrypt()
    {
        return $this->needEncrypt;
    }

}
