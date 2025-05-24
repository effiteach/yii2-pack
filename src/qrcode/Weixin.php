<?php

namespace app\pack\qrcode;

use app\helper\Dir;
use app\helper\File;
use Yii;

class Weixin
{
    /**
     * $res = get_pack('qrcode.weixin', 'get', [$scene,$page,$width=300]);
     */
    public static function get($scene, $page = 'pages/index/index', $width = 200)
    {
        if (substr($page, 0, 1) == '/') {
            $page = substr($page, 1);
        }
        $app = get_ability('login.weixin', 'init');
        try {
            $env = get_param('weixin_env');
            $response = $app->getClient()->postJson('/wxa/getwxacodeunlimit', [
                'scene' => $scene,
                'page' => $page,
                'width' => $width,
                'check_path' => false,
                'env_version' => trim($env),
            ]);
            $res = $response->getContent();
            $arr = @json_decode($res, true);
            $errmsg = $arr['errmsg'] ?? '';
            if ($errmsg) {
                yii_error($errmsg);
                return;
            }
            $md = md5($scene . $page . $width . $env);
            $url = '/uploads/qrcode/weixin.minapp/' . $md . '.png';
            $file = Yii::getAlias('@webroot' . $url);
            $dir = File::getDir($file);
            Dir::create($dir);
            if (!file_exists($file)) {
                $response->saveAs($file);
            }
            return $url;
        } catch (\Throwable $e) {
            $err = $e->getMessage();
            yii_error($err);
        }
    }
}
