<?php


/**
 * 腾讯云短信
 * https://cloud.tencent.com/document/product/382/43195
 */

namespace app\pack\sms;

// 导入对应产品模块的client
use TencentCloud\Common\Credential;
// 导入要请求接口对应的Request类
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
// 导入可选配置类
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\SmsClient;
use Yii;
use app\helper\CurlData;

class Tencent
{
    public static $client;
    public static $req;
    public static function send($phone, $template_id, $data = [], $sign_name = null)
    {
        $template_id = get_sms_template('tencent', $template_id);
        self::getClient($sign_name);
        $client = self::$client;
        $req = self::$req;
        /* 模板 ID: 必须填写已审核通过的模板 ID */
        // 模板 ID 可前往 [国内短信](https://console.cloud.tencent.com/smsv2/csms-template) 或 [国际/港澳台短信](https://console.cloud.tencent.com/smsv2/isms-template) 的正文模板管理查看
        $req->TemplateId = $template_id;
        /* 模板参数: 模板参数的个数需要与 TemplateId 对应模板的变量个数保持一致，若无模板参数，则设置为空*/
        if ($data) {
            $data = array_values($data);
            $req->TemplateParamSet = $data;
        }
        /* 下发手机号码，采用 E.164 标准，+[国家或地区码][手机号]
         * 示例如：+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号*/
        $req->PhoneNumberSet = array($phone);
        /* 用户的 session 内容（无需要可忽略）: 可以携带用户侧 ID 等上下文信息，server 会原样返回 */
        $req->SessionContext = "";
        /* 短信码号扩展号（无需要可忽略）: 默认未开通，如需开通请联系 [腾讯云短信小助手] */
        $req->ExtendCode = "";
        /* 国内短信无需填写该项；国际/港澳台短信已申请独立 SenderId 需要填写该字段，默认使用公共 SenderId，无需填写该字段。注：月度使用量达到指定量级可申请独立 SenderId 使用，详情请联系 [腾讯云短信小助手](https://cloud.tencent.com/document/product/382/3773#.E6.8A.80.E6.9C.AF.E4.BA.A4.E6.B5.81)。*/
        $req->SenderId = "";
        // 通过client对象调用SendSms方法发起请求。注意请求方法名与请求对象是对应的
        // 返回的resp是一个SendSmsResponse类的实例，与请求对象对应
        try {
            $resp = $client->SendSms($req);
            $res = $resp->toJsonString();
            $arr = json_decode($res, true);
            $fee = $arr['SendStatusSet'][0]['Fee'] ?? '';
            $msg = $arr['SendStatusSet'][0]['Message'] ?? '';
            if ($fee == 0) {
                yii_error('tencent:' . $msg);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            $err = $e->getMessage();
            yii_error('tencent:' . $err);
            return false;
        }
    }

    public static function less()
    {
        return false;
    }
    public static function get_config()
    {
        return Yii::$app->params['sms']['tencent'];
    }
    private static function getClient($sign_name = '')
    {
        $smsConfig = self::get_config();
        // 实例化一个证书对象，入参需要传入腾讯云账户 SecretId，SecretKey
        // 为了保护密钥安全，建议将密钥设置在环境变量中或者配置文件中。
        // 硬编码密钥到代码中有可能随代码泄露而暴露，有安全隐患，并不推荐。
        // SecretId、SecretKey 查询: https://console.cloud.tencent.com/cam/capi
        // $cred = new Credential("SecretId", "SecretKey");
        $cred = new Credential(get_config('tencent_secret_id'), get_config('tencent_secret_key'));
        // 实例化一个http选项，可选的，没有特殊需求可以跳过
        $httpProfile = new HttpProfile();
        // 配置代理（无需要直接忽略）
        // $httpProfile->setProxy("https://ip:port");
        $httpProfile->setReqMethod("GET"); // get请求(默认为post请求)
        $httpProfile->setReqTimeout(10); // 请求超时时间，单位为秒(默认60秒)
        $httpProfile->setEndpoint("sms.tencentcloudapi.com"); // 指定接入地域域名(默认就近接入)
        // 实例化一个client选项，可选的，没有特殊需求可以跳过
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("TC3-HMAC-SHA256"); // 指定签名算法
        $clientProfile->setHttpProfile($httpProfile);
        // 第二个参数是地域信息，可以直接填写字符串ap-guangzhou，支持的地域列表参考 https://cloud.tencent.com/document/api/382/52071#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8
        $client = new SmsClient($cred, $smsConfig['template_area'], $clientProfile);
        $req = new SendSmsRequest();
        /* 短信应用ID: 短信SdkAppId在 [短信控制台] 添加应用后生成的实际SdkAppId，示例如1400006666 */
        // 应用 ID 可前往 [短信控制台](https://console.cloud.tencent.com/smsv2/app-manage) 查看
        $req->SmsSdkAppId = $smsConfig['sdk_app_id'];
        /* 短信签名内容: 使用 UTF-8 编码，必须填写已审核通过的签名 */
        // 签名信息可前往 [国内短信](https://console.cloud.tencent.com/smsv2/csms-sign) 或 [国际/港澳台短信](https://console.cloud.tencent.com/smsv2/isms-sign) 的签名管理查看
        $req->SignName = $sign_name ?: $smsConfig['sign_name'];
        self::$req = $req;
        self::$client = $client;
    }

}
