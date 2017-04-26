FK Pay
======
Integration of all Third Part Payment(TPP)

Configuration
-------------

```php
# main-local.php
<?php
return [
    // other config
    'components' => [
        'pay' => [
            'class' => 'fk\pay\Component',
            'channel' => 'WeChat', // Here is default channel
            // As for different platforms, the actual notify_url will need a prefix of platform name
            // end with .php
            // e.g.
            // For WeChat, it will be `https://api.alijian.net/notify/we-chat.php`
            // The `we-chat.php` come from `WeChat`.
            // You should rewrite the server route, to fit the filename rule.
            'notifyPath' => 'https://api-test.alijian.net/notify/',
            'platforms' => [
                'WeChat' => [
                    'js' => [ // Web, a.k.a JS, a.k.a H5 payment
                        'app_id' => 'wx71xxx',
                        'app_secret' => 'xxx',
                        'mch_id' => 1234567890,
                        'key' => 'QqDAWHMgDpskKmsdjYH', // to sign
                        'ssl_cert_path' => '@common/data/cache/apiclient_cert.pem', // to refund
                        'ssl_key_path' => '@common/data/cache/apiclient_key.pem',
                    ],
                    'app' => [ // App(iOS, Android) payment
                        'app_id' => 'wxxxxx',
                        'mch_id' => 1234567890,
                        'app_secret' => '6exxx',
                        'key' => 'QqDAWHMgDpskKmsdjYH', // to sign
                    ],
                    'enterprise' => [ // Enterprise transfer
                        'app_id' => 'wx71aaf2ls3j9dj4hs8',
                        'mch_id' => 1234567890,
                        'key' => 'QqDAWHMgDpskKmsdjYH', // to sign
                        'ssl_cert_path' => '@common/data/cache/apiclient_cert.pem', // to refund
                        'ssl_key_path' => '@common/data/cache/apiclient_key.pem',
                    ]
                ],
                'AliPay' => [ // under construction
                    'name' => 'haha',
                ],
            ]
        ]
    ]
];

```
Pay
---
Transfer money from user's TPP account to your platform
```php
<?php
$orderSn = '2014124582620133'; // 20 bytes recommended
$amount = 1000; // Money with unit Fen(CNY), 10 Yuan
$name = 'Apple';
$description = 'Sweet Apple';
$extra = [
    'trade_type' => 'JSAPI',
    'openid' => 'o-28js7h4kd01kldfg7ag29zk3'
];
Yii::$app->pay
    ->with('WeChat') // Default defined in config file
    ->pay($orderSn, $amount, $name, $description, $extra);
```
| Parameter | Description|
|---|---|
|$orderSn   | Order Serial Number on your platform|
|$amount    | Money of the goods|
|$name      | Name of the goods|
|$description| Description for the goods|
|$extra     | Extra information for the order, differs with TPP|

### $extra for Wechat
- **trade_type**: required APP, JSAPI, NATIVE
- **openid**: required when trade_type=JSAPI
- **...**: other optional params for WeChat API, see
    [JSAPI Docs](https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1)
    and
    [APP Docs](https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1)

### $extra for AliPay