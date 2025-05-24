<?php

namespace app\pack\safe\Baidu;

use app\helper\CurlData;
use Yii;

/**
 * 内容安全
 * https://console.bce.baidu.com/ai/#/ai/antiporn/app/list
 */
class Text
{
    public static $client;
    public static function init()
    {
        self::$client = new \AipContentCensor(get_param('baidu_antiporn_app_id'), get_param('baidu_antiporn_app_key'), get_param('baidu_antiporn_app_secret'));
        return self::$client;
    }

    /**
     * $res = pack('safe.text','check',[$content]);
     */
    public static function check($content)
    {
        $key = md5($content);
        $cache_id = "TextSafe:" . $key;
        $cache_data = CurlData::getData($cache_id, 'TextSafe');
        if ($cache_data) {
            return self::data($cache_data);
        }
        $log_id = CurlData::addLog('百度云-文本安全', 'TextSafe', ['content' => $content]);
        $arr = self::init()->textCensorUserDefined($content);
        if (isset($arr['error_code'])) {
            yii_error($arr);
            return;
        }
        CurlData::addDataUnique($cache_id, 'TextSafe', $arr);
        return self::data($arr);
    }

    public static function data($arr)
    {
        $conclusion = $arr['conclusion'] ?? '';
        if ($conclusion == '合规') {
            return true;
        }
        return false;
    }
}
