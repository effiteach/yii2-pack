<?php

/** 
 * Copyright (c) 2025 WeChat: sunkangchina. All rights reserved.  
 * @license https://github.com/sunkangchina/license
 */

namespace app\pack\captcha;

use TencentCloud\Captcha\V20190722\CaptchaClient;
use TencentCloud\Captcha\V20190722\Models\DescribeCaptchaResultRequest;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use app\helper\Env;

class Tencent
{
    public static $form = 'form';
    public $config;
    public $type = 'app';
    public function __construct()
    {
        $this->config = get_param('captcha')['tencent'];
    }
    public function check($ignore_expire = false)
    {
        if ($this->type == 'xcx') {
            $this->config['captcha_app_id'] = $this->config['captcha_xcx_id'];
            $this->config['captcha_app_key'] = $this->config['captcha_xcx_key'];
        }
        try {
            // 实例化一个认证对象，入参需要传入腾讯云账户 SecretId 和 SecretKey，此处还需注意密钥对的保密
            // 代码泄露可能会导致 SecretId 和 SecretKey 泄露，并威胁账号下所有资源的安全性。以下代码示例仅供参考，建议采用更安全的方式来使用密钥，请参见：https://cloud.tencent.com/document/product/1278/85305
            // 密钥可前往官网控制台 https://console.cloud.tencent.com/cam/capi 进行获取
            $id = get_config('tencent_secret_id');
            $key = get_config('tencent_secret_key');
            $app_key = $this->config['captcha_app_key'];
            if (!$id || !$key) {
                return json_error(['msg' => '缺少 secret_id secret_key 可至https://console.cloud.tencent.com/cam/capi 进行获取']);
            }
            $cred = new Credential($id, $key);
            // 实例化一个http选项，可选的，没有特殊需求可以跳过
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("captcha.tencentcloudapi.com");

            // 实例化一个client选项，可选的，没有特殊需求可以跳过
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            // 实例化要请求产品的client对象,clientProfile是可选的
            $client = new CaptchaClient($cred, "", $clientProfile);
            // 实例化一个请求对象,每个接口都会对应一个request对象
            $req = new DescribeCaptchaResultRequest();
            $input = Env::getInput('captcha');
            $app_id = (int) $input['appid'];
            $captcha_app_id = (int) get_param('tencent_captcha_app_id');
            if (!$app_id) {
                $app_id = $captcha_app_id;
            }
            $ticket = $input['ticket'];
            $randstr = $input['randstr'];
            if (!$ticket || !$randstr) {
                throw new \Exception("请点击验证码", 505);
            }
            $params = array(
                "CaptchaType" => 1,
                "Ticket" => $ticket,
                "UserIp" => Env::getIp(),
                "Randstr" => $randstr,
                "CaptchaAppId" => $app_id,
                "AppSecretKey" => $app_key,
            );
            $req->fromJsonString(json_encode($params));
            // 返回的resp是一个DescribeCaptchaResultResponse的实例，与请求对象对应
            $resp = $client->DescribeCaptchaResult($req);
            // 输出json格式的字符串回包
            $res = json_decode($resp->toJsonString(), true);
            $msg = $res['CaptchaMsg'];
            if ($msg == 'OK') {
                return;
            }
            if ($msg) {
                yii_error("tencent.captcha: " . $msg);
                throw new \Exception($msg);
            }
        } catch (TencentCloudSDKException $e) {
            $msg = $e->getMessage();
            yii_error("tencent.captcha: " . $msg);
            if ($ignore_expire && strpos($msg, '户已欠费') !== false) {
                return;
            }
            throw new \Exception($msg);
        }
    }

    public function get()
    {
        return [
            'button' => $this->button(),
            'js_file' => $this->js_file(),
            'js_code' => $this->js_code(),
        ];
    }

    public function button($str = '验证')
    {
        return '<div id="t_captcha_input" class="captcha_clould" style="display:none;" >' . $str . '</div>';
    }

    public function js_file()
    {
        return '<script src="https://turing.captcha.qcloud.com/TCaptcha.js"></script>';
    }

    public function js_code()
    {
        $form = self::$form;
        $captcha_app_id = $this->config['captcha_app_id'];
        if (!$captcha_app_id) {
            echo "tencent_captcha_app_id 未配置，请访问 https://console.cloud.tencent.com/captcha/graphical";
            exit;
        }
?>
        <script>
            // 定义回调函数
            function callback(res) {
                // 第一个参数传入回调结果，结果如下：
                // ret         Int       验证结果，0：验证成功。2：用户主动关闭验证码。
                // ticket      String    验证成功的票据，当且仅当 ret = 0 时 ticket 有值。
                // CaptchaAppId       String    验证码应用ID。
                // bizState    Any       自定义透传参数。
                // randstr     String    本次验证的随机串，后续票据校验时需传递该参数。
                console.log('callback:', res);
                // res（用户主动关闭验证码）= {ret: 2, ticket: null}
                // res（验证成功） = {ret: 0, ticket: "String", randstr: "String"}
                // res（请求验证码发生错误，验证码自动返回terror_前缀的容灾票据） = {ret: 0, ticket: "String", randstr: "String",  errorCode: Number, errorMessage: "String"}
                // 此处代码仅为验证结果的展示示例，真实业务接入，建议基于ticket和errorCode情况做不同的业务处理
                if (res.ret === 0) {
                    app.<?= $form ?>.randstr = res.randstr;
                    app.<?= $form ?>.ticket = res.ticket;
                    app.<?= $form ?>.appid = res.appid;
                    app.after_captcha();
                }
            }

            // 定义验证码js加载错误处理函数
            function loadErrorCallback() {
                var appid = <?= $captcha_app_id ?>;
                // 生成容灾票据或自行做其它处理
                var ticket = 'terror_1001_' + appid + '_' + Math.floor(new Date().getTime() / 1000);
                callback({
                    ret: 0,
                    randstr: '@' + Math.random().toString(36).substr(2),
                    ticket: ticket,
                    errorCode: 1001,
                    errorMessage: 'jsload_error'
                });
            }

            // 定义验证码触发事件
            window.onload = function() {
                document.getElementById('t_captcha_input').onclick = function() {
                    try {
                        // 生成一个验证码对象
                        // CaptchaAppId：登录验证码控制台，从【验证管理】页面进行查看。如果未创建过验证，请先新建验证。注意：不可使用客户端类型为小程序的CaptchaAppId，会导致数据统计错误。
                        //callback：定义的回调函数
                        var captcha = new TencentCaptcha('<?= $captcha_app_id ?>', callback, {});
                        // 调用方法，显示验证码
                        captcha.show();
                    } catch (error) {
                        // 加载异常，调用验证码js加载错误处理函数
                        loadErrorCallback();
                    }
                }
            }
        </script>
<?php
    }
}
