<?php

/* *
 * 功能：支付宝手机网站alipay.trade.close (统一收单交易关闭接口)业务参数封装
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */

//require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . './../../AopSdk.php';
//require dirname(__FILE__) . DIRECTORY_SEPARATOR . './../../config.php';

namespace fk\pay\lib\AliPay\wap\service;

use \Exception;
use fk\pay\lib\AliPay\aop\AopClient;
use fk\pay\lib\AliPay\aop\request\AliPayTradeWapPayRequest;
use fk\pay\lib\AliPay\wap\builders\BuilderInterface;

class AliPayTradeService
{

    //支付宝网关地址
    public $gateway_url = "https://openapi.alipay.com/gateway.do";

    //支付宝公钥
    public $alipay_public_key;

    //商户私钥
    public $private_key;

    //应用id
    public $appid;

    //编码格式
    public $charset = "UTF-8";

    public $token = null;

    //返回数据格式
    public $format = "json";

    //签名方式
    public $signtype = "RSA";

    protected $redirectWithHtml;

    public function __construct($aliPayConfig)
    {
        $this->appid = trim($aliPayConfig['app_id']);
        $this->private_key = $aliPayConfig['merchant_private_key'];
        $this->charset = $aliPayConfig['charset'];
        $this->signtype = $aliPayConfig['sign_type'];
        $this->gateway_url = $aliPayConfig['gatewayUrl'];
        $this->alipay_public_key = $aliPayConfig['alipay_public_key'];
        $this->redirectWithHtml = $aliPayConfig['redirectWithHtml'] ?? true;

        if (empty($this->appid)) {
            throw new Exception("App ID should not be empty!");
        }
        if (empty($this->private_key) || trim($this->private_key) == "") {
            throw new Exception("private_key should not be empty!");
        }
        if (empty($this->alipay_public_key) || trim($this->alipay_public_key) == "") {
            throw new Exception("AliPay public key should not be empty!");
        }
        if (empty($this->charset) || trim($this->charset) == "") {
            throw new Exception("charset should not be empty!");
        }
        if (empty($this->gateway_url) || trim($this->gateway_url) == "") {
            throw new Exception("gateway_url should not be empty!");
        }

    }

    public function AliPayWapPayService($aliPayConfig)
    {
        $this->__construct($aliPayConfig);
    }

    /**
     * alipay.trade.wap.pay
     * @param BuilderInterface $builder 业务参数，使用buildmodel中的对象生成。
     * @param string $return_url 同步跳转地址，公网可访问
     * @param string $notify_url 异步通知地址，公网可以访问
     * @return mixed $response 支付宝返回的信息
     */
    public function wapPay($builder, $return_url, $notify_url)
    {

        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);

        $request = new AliPayTradeWapPayRequest();

        $request->setNotifyUrl($notify_url);
        $request->setReturnUrl($return_url);
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopClientRequestExecute($request, $this->redirectWithHtml);
        return $response;
    }

    public function aopClientRequestExecute($request, $outputHtml = false)
    {

        $aop = new AopClient();
        $aop->gatewayUrl = $this->gateway_url;
        $aop->appId = $this->appid;
        $aop->rsaPrivateKey = $this->private_key;
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $aop->apiVersion = "1.0";
        $aop->postCharset = $this->charset;
        $aop->format = $this->format;
        $aop->signType = $this->signtype;
        // 开启页面信息输出
        $aop->debugInfo = true;
        if ($outputHtml) {
            $result = $aop->pageExecute($request, "post");
        } else {
            $result = $aop->execute($request);

            //打开后，将报文写入log文件
            $this->writeLog("response: " . var_export($result, true));
        }
        return $result;
    }

    /**
     * alipay.trade.query (统一收单线下交易查询)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function Query($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeQueryRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_query_response;
        var_dump($response);
        return $response;
    }

    /**
     * alipay.trade.refund (统一收单交易退款接口)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function Refund($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeRefundRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_refund_response;
        var_dump($response);
        return $response;
    }

    /**
     * alipay.trade.close (统一收单交易关闭接口)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function Close($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeCloseRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_close_response;
        var_dump($response);
        return $response;
    }

    /**
     * 退款查询   alipay.trade.fastpay.refund.query (统一收单交易退款查询)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function refundQuery($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new AlipayTradeFastpayRefundQueryRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        var_dump($response);
        return $response;
    }

    /**
     * alipay.data.dataservice.bill.downloadurl.query (查询对账单下载地址)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    public function downloadUrlQuery($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        $this->writeLog($biz_content);
        $request = new alipaydatadataservicebilldownloadurlqueryRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_data_dataservice_bill_downloadurl_query_response;
        var_dump($response);
        return $response;
    }

    /**
     * 验签方法
     * @param array $arr 验签支付宝返回的信息，使用支付宝公钥。
     * @return boolean
     */
    public function check($arr)
    {
        $aop = new AopClient();
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $result = $aop->rsaCheckV1($arr, $this->alipay_public_key, $this->signtype);
        return $result;
    }

    //请确保项目文件有可写权限，不然打印不了日志。
    public function writeLog($text)
    {
        // $text=iconv("GBK", "UTF-8//IGNORE", $text);
        //$text = characet ( $text );
        file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "./../../log.txt", date("Y-m-d H:i:s") . "  " . $text . "\r\n", FILE_APPEND);
    }


    /**
     * 利用google api生成二维码图片
     * $content：二维码内容参数
     * $size：生成二维码的尺寸，宽度和高度的值
     * $lev：可选参数，纠错等级
     * $margin：生成的二维码离边框的距离
     */
    public function create_erweima($content, $size = '200', $lev = 'L', $margin = '0')
    {
        $content = urlencode($content);
        $image = '<img src="http://chart.apis.google.com/chart?chs=' . $size . 'x' . $size . '&amp;cht=qr&chld=' . $lev . '|' . $margin . '&amp;chl=' . $content . '"  widht="' . $size . '" height="' . $size . '" />';
        return $image;
    }
}