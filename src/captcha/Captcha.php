<?php

namespace app\pack\captcha;

use Yii;

class Captcha
{
    public static function init()
    {
        $name = get_param('captcha')['drive'];
        $cls = "\\app\\pack\\captcha\\" . ucfirst($name);
        if (class_exists($cls)) {
            return new $cls();
        }
    }
    /**
     * get_pack('captcha.Captcha', 'verify', [$ignore_expire]);
     */
    public static function verify($type = 'app')
    {
        $obj = self::init();
        $obj->type = $type;
        return $obj->check();
    }

    public static function js()
    {
        Yii::$app->view->register("
            $('#t_captcha_input').trigger('click');
        ");
    }
}
