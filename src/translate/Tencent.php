<?php

/**
 * 腾讯云 文本翻译
 * 购买，有免费额度
 * https://console.cloud.tencent.com/tmt
 * 文档
 * https://cloud.tencent.com/document/product/551/15619
 */

namespace app\pack\translate;

use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Tmt\V20180321\Models\TextTranslateRequest;
use TencentCloud\Tmt\V20180321\TmtClient;
use Yii;

class Tencent
{
    public $convert = [
        'zh-cn' => 'zh',
    ];
    /**
     * 翻译
     * @param $SourceText
     * @param string $Source
     * @param string $Target
     * @return string
     */
    public function translate($SourceText, $Source = 'zh', $Target = 'en')
    {
        $cacheKey = "Translate.Tencent." . md5($SourceText . $Source . $Target);
        $result = Yii::$app->cache->get($cacheKey);
        if ($result) {
            return $result;
        }
        try {
            $Source = $this->convert[$Source] ?? $Source;
            $Target = $this->convert[$Target] ?? $Target;
            $cred = new Credential(Yii::$app->params['tencent_secret_id'], Yii::$app->params['tencent_secret_key']);
            // 实例化一个http选项，可选的，没有特殊需求可以跳过
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("tmt.tencentcloudapi.com");
            // 实例化一个client选项，可选的，没有特殊需求可以跳过
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            // 实例化要请求产品的client对象,clientProfile是可选的
            $client = new TmtClient($cred, "ap-beijing", $clientProfile);
            // 实例化一个请求对象,每个接口都会对应一个request对象
            $req = new TextTranslateRequest();
            $params = array(
                "SourceText" => $SourceText,
                "Source" => $Source,
                "Target" => $Target,
                "ProjectId" => 0,
            );
            $req->fromJsonString(json_encode($params));
            // 返回的resp是一个TextTranslateResponse的实例，与请求对象对应
            $resp = $client->TextTranslate($req);
            // 输出json格式的字符串回包
            $res = $resp->toJsonString();
            $res = json_decode($res, true);
            $TargetText = $res['TargetText'];
            if ($TargetText) {
                Yii::$app->cache->set($cacheKey, $TargetText);
                return $TargetText;
            }
        } catch (TencentCloudSDKException $e) {
            yii_error($e->getMessage());
            return '';
        }
    }
}
