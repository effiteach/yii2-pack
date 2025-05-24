<?php

namespace app\pack\safe\Aliyun;

use AlibabaCloud\Green\Green;
use app\helper\CurlData;
use Yii;

/**
 * 内容安全
 * https://yundun.console.aliyun.com/?p=cts
 */
class Image extends Text
{
    /**
     * $res = pack('safe.image','check',[$url]);
     */
    public static function check($url)
    {
        $key = md5($url);
        $cache_id = "ImageSafe:" . $key;
        $cache_data = CurlData::getData($cache_id, 'ImageSafe');
        if ($cache_data) {
            return self::data($cache_data);
        }
        $log_id = CurlData::addLog('阿里云-图片安全', 'ImageSafe', ['url' => $url]);
        init_aliyun_sdk();
        $task1 = array(
            'dataId' => $key,
            'url' => $url,
        );
        $response = Green::v20180509()->imageSyncScan()
            ->timeout(10) // 超时10秒，request超时设置，仅对当前请求有效。
            ->connectTimeout(3) // 连接超时3秒，当单位小于1，则自动转换为毫秒，request超时设置，仅对当前请求有效。
            ->body(json_encode(
                array(
                    'tasks' => array($task1),
                    'scenes' => array(
                        'porn',
                        'terrorism',
                    ),
                    'bizType' => 'default',
                )
            ))
            ->request();
        $arr = $response->toArray();
        if ($arr['code'] != 200) {
            yii_error($arr);
            return;
        }

        CurlData::addDataUnique($cache_id, 'ImageSafe', $arr);
        return self::data($arr);
    }

}
