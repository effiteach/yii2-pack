<?php

namespace app\pack\oss;

class Oss
{
    public static function init()
    {
        $c = get_config('oss_drive') ?: 'Tencent';
        $cls = '\\app\\pack\\oss\\' . ucfirst($c);
        return $cls;
    }

    public static function upload($file, $object = '')
    {
        $cls = self::init();
        return $cls::upload($file, $object);
    }

    public static function getObjectUrl($object)
    {
        if (substr($object, 0, 1) != '/') {
            $object = '/' . $object;
        }
        return $object;
    }
    public static function getObject($file, $object)
    {
        $object = str_replace(PATH, '', $file);
        if (substr($object, 0, 4) == '/web') {
            $object = substr($object, 4);
        }
        return $object;
    }
}
