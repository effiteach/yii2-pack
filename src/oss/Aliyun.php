<?php

namespace app\pack\oss;

/**
 * composer require aliyuncs/oss-sdk-php
 * https://github.com/aliyun/aliyun-oss-php-sdk
 */

use OSS\OssClient;

class Aliyun
{
    public static $obj;
    public static $oss_url = "";
    public static $bucket = "";

    /**
     * 获取私有签名URL，1小时过期
     */
    public static function getUrl($url, $bucket = '')
    {
        $arr = self::_comm_sts();
        $accessId = $arr['accessId'];
        $accessKey = $arr['accessKey'];
        $SecurityToken = $arr['SecurityToken'];
        $endPoint = $arr['endPoint'];
        $options = array("response-content-disposition" => "inline");
        $ossClient = new OssClient($accessId, $accessKey, $endPoint, false, $SecurityToken);
        $signedUrl = $ossClient->signUrl($bucket, $url, 3600, "GET", $options);
        return $signedUrl;
    }

    public static function initAliyun()
    {
        if (!self::$obj) {
            $accessId = get_config('aliyun_accesskey_id');
            $accessKey = get_config('aliyun_accesskey_secret');
            $endPoint = get_config('oss')['aliyun']['endpoint'];
            self::$bucket = get_config('oss')['aliyun']['bucket'];
            self::$obj = new OssClient($accessId, $accessKey, $endPoint);
        }
        return self::$obj;
    }
    /**
     * 上传文件到阿里云OSS
     *
     * @param string $file
     * @param string $content
     * @return void
     */
    public static function upload($file, $object = '')
    {
        $object = Oss::getObject($file, $object);
        if (!file_exists($file)) {
            yii_error("oss baid file not exists");
            return;
        }
        if (substr($object, 0, 1) == '/') {
            $object = substr($object, 1);
        }
        $content = file_get_contents($file);
        $mime = mime_content_type($file);
        $options['content-type'] = $mime;
        $ossClient = self::initAliyun();
        $bucket_name = self::$bucket;
        //所有bucket
        $bucketListInfo = $ossClient->listBuckets();
        $bucketList = $bucketListInfo->getBucketList();
        $arr = [];
        foreach ($bucketList as $bucket) {
            $name = (string) $bucket->getName();
            $arr[$name] = $name;
        }
        if (!$arr[$bucket_name]) {
            $ossClient->createBucket($bucket_name);
        }
        $res = $ossClient->putObject($bucket_name, $object, $content, $options);
        if ($res['info']['url']) {
            $url = Oss::getObjectUrl($object);
            return $url;
        } else {
            return;
        }
    }
    public static function lists()
    {
        $ossClient = self::initAliyun();
        $listObjectInfo = $ossClient->listObjects(self::$bucket, [
            OssClient::OSS_MAX_KEYS => 1000,
            'delimiter' => '',
        ]);
        $objectList = $listObjectInfo->getObjectList();
        $key = [];
        foreach ($objectList as $objectInfo) {
            $key[] = $objectInfo->getKey();
        }
        return $key;
    }

}
