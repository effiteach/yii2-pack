<?php

namespace app\pack\oss;

use app\helper\File;
use UpYun as UpYunDrive;
/**
 * https://github.com/upyun/php-sdk
 */
use Yii;

class Upyun
{
    public static function init()
    {
        $c = get_config('oss')['upyun'];
        $client = new UpYunDrive($c['server_name'], $c['account'], $c['password']);
        return $client;
    }
    /**
     * 上传
     */
    public static function upload($file, $object = '')
    {
        $client = self::init();
        $object = Oss::getObject($file, $object);
        if (!file_exists($file)) {
            yii_error("oss upload:" . $file . " not exist");
            return;
        }
        if (substr($object, 0, 1) != '/') {
            $object = '/' . $object;
        }
        $content = file_get_contents($file);
        $mime = mime_content_type($file);
        $options['content-type'] = $mime;
        try {
            $res = $client->writeFile($object, $content);
            if ($res) {
                $url = Oss::getObjectUrl($object);
                return $url;
            }
        } catch (\Exception $e) {
            yii_error("oss tencent upload:" . $e->getMessage());
        }
    }
    /**
     * 列表
     */
    public static function lists($path = '/')
    {
        $client = self::init();
        $all = $client->getList($path);
        $list = [];
        foreach ($all as $v) {
            if ($v['type'] == 'folder') {
                $new_path = $path . $v['name'] . '/';
                $list = array_merge($list, self::lists($new_path));
            } else {
                $list[] = $path . $v['name'];
            }
        }
        return $list;
    }

    /**
     * 删除
     */
    public static function deleteAll()
    {
        $client = self::init();
        $all = self::lists();
        if ($all) {
            $dirs = [];
            foreach ($all as $v) {
                $dirs[] = File::getDir($v);
                $client->delete($v);
            }
            foreach ($dirs as $dir) {
                $client->delete($dir);
            }
        }
    }

}
