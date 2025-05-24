<?php

namespace app\pack\safe;

use Yii;

/**
 * 内容安全
 */
class Text
{
    /**
     * $res = pack('safe.text','check',[$content]);
     */
    public static function check($content)
    {
        $drive = get_param('safe_drive') ?: 'Aliyun';
        $cls = "\app\pack\safe\\" . ucfirst($drive) . "\\Text";
        try {
            return $cls::check($content);
        } catch (\Throwable $th) {
            yii_error("文本安全调用：" . $drive . ",发生一个错误" . $th->getMessage());
        }
    }

}
