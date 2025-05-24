<?php

/**
 * 云通信语音服务
 * 虚拟号码
 *
 * https://help.aliyun.com/zh/vms/double-call-voice?spm=5176.2020520104.0.0.3667709aenuyG3#title-v8r-qxq-01j
 *
 * https://next.api.aliyun.com/api/Dyvmsapi/2017-05-25/QueryVirtualNumberRelation?params={%22ProdCode%22:%22dyvms%22,%22RouteType%22:1}
 */

namespace app\pack\virtual_number;

use app\helper\Curl;
use Yii;
use AlibabaCloud\SDK\Dyvmsapi\V20170525\Dyvmsapi;
use Exception;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dyvmsapi\V20170525\Models\AddVirtualNumberRelationRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;

class virtual_number
{
    public static function createClient()
    {
        // 建议使用更安全的 STS 方式，更多鉴权访问方式请参见：https://help.aliyun.com/document_detail/311677.html。
        $config = new Config([
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_ID。
            "accessKeyId" => getenv("ALIBABA_CLOUD_ACCESS_KEY_ID"),
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_SECRET。
            "accessKeySecret" => getenv("ALIBABA_CLOUD_ACCESS_KEY_SECRET")
        ]);
        // Endpoint 请参考 https://api.aliyun.com/product/Dyvmsapi
        $config->endpoint = "dyvmsapi.aliyuncs.com";
        return new Dyvmsapi($config);
    }
    /**
     * 查询虚拟号码列表
     */
    public static function seach($pageNo = 1)
    {
        $client = self::createClient();
        $client = self::createClient();
        $queryVirtualNumberRequest = new QueryVirtualNumberRequest([
            "prodCode" => "dyvms",
            "routeType" => 1,
            "pageNo" => $pageNo,
            "pageSize" => 10
        ]);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $res = $client->queryVirtualNumberWithOptions($queryVirtualNumberRequest, $runtime);
            pr($res);
        } catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 此处仅做打印展示，请谨慎对待异常处理，在工程项目中切勿直接忽略异常。
            // 错误 message
            var_dump($error->message);
            // 诊断地址
            var_dump($error->data["Recommend"]);
            Utils::assertAsString($error->message);
        }
    }
    /**
     * 生成双向通话号码
     */
    public static function create($name = [], $real_phone_number = [], $virtual_phone_number = [])
    {
        $client = self::createClient();
        $addVirtualNumberRelationRequest = new AddVirtualNumberRelationRequest([
            "prodCode" => "dyvms",//产品名称
            "corpNameList" => implode(',', $name),//公司名列表
            "numberList" => implode(',', $real_phone_number), //真实号码列表
            "routeType" => 1, //路由类型
            "phoneNum" => implode(',', $virtual_phone_number), //虚拟号码
        ]);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $client->addVirtualNumberRelationWithOptions($addVirtualNumberRelationRequest, $runtime);
        } catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            yii_error($error->message);
        }
    }
}
