<?php

namespace app\pack\oss;

use Yii;

/**
 * https://github.com/tencentyun/cos-php-sdk-v5
 */

class Tencent
{
    public static $bucket;
    public static function init()
    {
        $c = get_config('oss')['tencent'];
        self::$bucket = $c['bucket'];
        $secretId = get_config('tencent_secret_id');
        $secretKey = get_config('tencent_secret_key');
        $region = $c['region'];
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $region,
                'schema' => 'https',
                'credentials' => array(
                    'secretId' => $secretId,
                    'secretKey' => $secretKey,
                ),
            )
        );
        return $cosClient;
    }

    public static function create($bucket_name)
    {
        $cosClient = self::init();
        try {
            $bucket = $bucket_name; //存储桶名称 格式：BucketName-APPID
            $result = $cosClient->createBucket(array('Bucket' => $bucket));
            //请求成功
        } catch (\Exception $e) {
            yii_error("oss tencent create bucket fail:" . $e->getMessage());
        }
    }
    /**
     * 上传文件
     * @param $file 本地/uploads/……
     * @param $object 远程保存路径
     */
    public static function upload($file, $object = '')
    {
        $object = Oss::getObject($file, $object);
        if (!file_exists($file)) {
            yii_error("oss upload:" . $file . " not exist");
            return;
        }
        if (substr($object, 0, 1) == '/') {
            $object = substr($object, 1);
        }
        $content = file_get_contents($file);
        $mime = mime_content_type($file);
        $options['ContentType'] = $mime;
        $cosClient = self::init();
        try {
            $bucket = self::$bucket;
            $res = $cosClient->upload(
                $bucket,
                $object,
                $content,
                $options
            );
            try {
                $url = $cosClient->getObjectUrlWithoutSign($bucket, $object);
                $url = Oss::getObjectUrl($object);
                return $url;
            } catch (\Exception $e) {
                yii_error("oss tencent upload:" . $e->getMessage());
            }
        } catch (\Exception $e) {
            yii_error("oss tencent upload:" . $e->getMessage());
        }
    }

    /**
     * 列表
     */
    public static function lists()
    {
        $cosClient = self::init();
        $keys = [];
        try {
            $bucket = self::$bucket; //存储桶名称 格式：BucketName-APPID
            $prefix = ''; //列出对象的前缀
            $marker = ''; //上次列出对象的断点
            while (true) {
                $result = $cosClient->listObjects(array(
                    'Bucket' => $bucket,
                    'Marker' => $marker,
                    'MaxKeys' => 1000, //设置单次查询打印的最大数量，最大为1000
                ));
                if (isset($result['Contents'])) {
                    foreach ($result['Contents'] as $rt) {
                        $keys[] = $rt['Key'];
                    }
                }
                $marker = $result['NextMarker']; //设置新的断点
                if (!$result['IsTruncated']) {
                    break; //判断是否已经查询完
                }
            }
        } catch (\Exception $e) {
            yii_error("oss tencent lists:" . $e->getMessage());
        }
        return $keys;
    }
    /**
     * 删除所有
     */
    public static function deleteAll()
    {
        $all = self::lists();
        $cosClient = self::init();
        $bucket = self::$bucket;
        if ($all) {
            $keys = [];
            foreach ($all as $key) {
                $keys[] = ['Key' => $key];
            }
            $res = $cosClient->deleteObjects(array(
                'Bucket' => $bucket,
                'Objects' => $keys,
            ));
            return $res;
        }
    }

    /**
     * 下载文件
     */
    public static function download($object)
    {
        $cosClient = self::init();
        $result = $cosClient->getObject(array(
            'Bucket' => $bucket,
            'Key' => $object));
        $body = $result['Body'];
        return $body;
    }
}
