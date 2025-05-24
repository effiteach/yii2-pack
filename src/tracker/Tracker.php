<?php

namespace app\pack\tracker;

class Tracker
{
    public static $class;
    /**
     * get_pack('tracker','init');
     */
    public static function init()
    {
        $drive = get_param('tracker');
        if ($drive) {
            $class = '\\app\\pack\\tracker\\'.ucfirst($drive);
            if (class_exists($class)) {
                self::$class = $class;
                $class::init();
            }
        }
    }
    /**
     * get_pack('tracker','info',[$msg]);
     */
    public static function info($msg)
    {
        $class = self::$class;
        if ($class) {
            if (is_array($msg)) {
                $msg = json_encode($msg);
            }
            if (is_string($msg)) {
                $class::info($msg);
            }
        }
    }
    /**
     * get_pack('tracker','error',[$msg]);
     */
    public static function error($msg)
    {
        $class = self::$class;
        if ($class) {
            if (is_array($msg)) {
                $msg = json_encode($msg);
            }
            if (is_string($msg)) {
                $class::error($msg);
            }
        }
    }
}
