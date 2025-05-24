<?php

namespace app\pack\safe\Aliyun;

use AlibabaCloud\Green\Green;
use app\helper\CurlData;
use Yii;

/**
 * 内容安全
 * https://yundun.console.aliyun.com/?p=cts
 */
class Text
{
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
        $log_id = CurlData::addLog('阿里云-文本安全', 'TextSafe', ['content' => $content]);
        init_aliyun_sdk();
        $task1 = array(
            'dataId' => $key,
            'content' => $content,
        );
        /**
         * 文本垃圾检测：antispam。
         **/
        $result = Green::v20180509()->textScan()
            ->timeout(10) // 超时10秒，request超时设置，仅对当前请求有效。
            ->connectTimeout(3) // 连接超时3秒，当单位小于1，则自动转换为毫秒，request超时设置，仅对当前请求有效。
            ->body(json_encode(array('tasks' => array($task1), 'scenes' => array('antispam'), 'bizType' => 'default')))
            ->request();
        $arr = $result->toArray();
        if ($arr['code'] != 200) {
            yii_error($arr);
            return;
        }
        CurlData::addDataUnique($cache_id, 'TextSafe', $arr);
        return self::data($arr);
    }

    public static function data($arr)
    {
        foreach ($arr['data'] as $k => $v) {
            foreach ($v['results'] as $k1 => $v1) {
                if ($v1['suggestion'] != 'pass') {
                    return false;
                }
            }
        }
        return true;
    }
}
