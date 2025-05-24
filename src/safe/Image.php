<?php

namespace app\pack\safe;

/**
 * 内容安全
 */
class Image
{
    /**
     * $res = pack('safe.image','check',[$url]);
     */
    public static function check($url)
    {
        $drive = get_param('safe_drive') ?: 'Aliyun';
        $cls = "\app\pack\safe\\" . ucfirst($drive) . "\\Image";
        try {
            return $cls::check($url);
        } catch (\Throwable $th) {
            yii_error("文本安全调用：" . $drive . ",发生一个错误" . $th->getMessage());
        }
    }

}
