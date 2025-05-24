<?php

namespace app\pack\safe\Baidu;

use app\helper\CurlData;
use Yii;

/**
 * 内容安全
 * https://console.bce.baidu.com/ai/#/ai/antiporn/app/list
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
        $log_id = CurlData::addLog('百度云-图片安全', 'ImageSafe', ['url' => $url]);
        $result = self::init()->imageCensorUserDefined($url);
        if (isset($arr['error_code'])) {
            yii_error($arr);
            return;
        }
        CurlData::addDataUnique($cache_id, 'ImageSafe', $arr);
        return self::data($arr);
    }

}
