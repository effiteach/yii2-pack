<?php


/**
 * 阿里云短信
 * https://dysms.console.aliyun.com/overview
 */

namespace app\pack\sms;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\Config;
use Exception;
use Yii;
use app\helper\CurlData;

class Aliyun
{
    public static $client;
    public static $req;
    public static function send($phone, $template_id, $data = [], $sign_name = null)
    {
        $template_id = get_sms_template('aliyun', $template_id);
        $smsConfig = self::get_config();
        $client = self::getClient();
        $par = [
            "phoneNumbers" => $phone,
            "signName" => $sign_name ?: $smsConfig['sign_name'],
            'templateCode' => $template_id,
        ];
        if ($data) {
            $par['templateParam'] = json_encode($data);
        }
        $sendSmsRequest = new SendSmsRequest($par);
        try {
            $res = $client->sendSms($sendSmsRequest, new RuntimeOptions([]));
            if (!$res->body->bizId) {
                yii_error("aliyun:" . $res->body->message);
                return false;
            }
            return true;
        } catch (Exception $error) {
            $err = $error->getMessage();
            yii_error("aliyun:" . $err);
        }
    }

    public static function less()
    {
        return false;
    }
    public static function get_config()
    {
        return Yii::$app->params['sms']['aliyun'];
    }
    private static function getClient($sign_name = '')
    {
        $smsConfig = self::get_config();
        $config = new Config([
            "accessKeyId" => get_config('aliyun_accesskey_id'),
            "accessKeySecret" => get_config('aliyun_accesskey_secret'),
        ]);
        $config->endpoint = $smsConfig['endpoint'];
        return new Dysmsapi($config);
    }

}
