# FK Pay V3

The v2.0 will not be maintained.

Integration of Third Part Payments(TPP)

## Configuration

```php
<?php
return [
    'WeChat' => [
        'web' => [ // Web, a.k.a JS, a.k.a H5 payment
            'app_id' => 'wx71xxx',
            'app_secret' => 'xxx',
            'mch_id' => 1234567890,
            'key' => 'QqDAWHMgDpskKmsdjYH', // to sign
            'ssl_cert_path' => 'path/to/apiclient_cert.pem', // To use enterprise paying to individual's balance
            'ssl_key_path' => '@common/data/cache/apiclient_key.pem',
        ],
        'app' => [
            // ... same as above
        ]
    ],
    'AliPay' => [ // under construction
        'h5' => [
            
        ]
    ],
];

```
## APIs

### Pay

To create an order and get the redirect params for actual payment

```php
<?php

$orderSn = '2014124582620133'; // 20 bytes recommended
$amount = 1000; // Money with unit Fen(CNY), 10 Yuan
$name = 'Apple';
$description = 'Sweet Apple';
$extra = [
    'trade_type' => 'JSAPI',
    'openid' => 'o-28js7h4kd08js7h4kd08js7h4kd0'
];
$pay = new \fk\pay\PayUniform($config); // refer to the previous part for $config
$pay->useWeChat()->pay($orderSn, $amount, $name, $description, $extra);
```

| Parameter | Description
|---        | ---        
|$orderSn   | Order Serial Number on your platform
|$amount    | Money of the goods
|$name      | Name of the goods
|$description| Description for the goods
|$extra     | Extra information for the order, differs with TPP

### $extra for Wechat
- **trade_type**: required APP, JSAPI, NATIVE
- **openid**: required when trade_type=JSAPI
- **...**: other optional params for WeChat API, see
    [JSAPI Docs](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1)
    and
    [APP Docs](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1)

### $extra for AliPay