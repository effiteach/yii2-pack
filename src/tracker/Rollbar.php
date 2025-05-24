<?php

/**
 * rollbar.com
 */

namespace app\pack\tracker;

use Rollbar\Payload\Level;

class Rollbar
{
    /**
     * get_pack('rollbar.rollbar','init');
     */
    public static function init()
    {
        $access_token = get_param('rollbar')['access_token'];
        if (!$access_token) {
            return;
        }
        $config = array(
            // required
            'access_token' => $access_token,
            // optional - environment name. any string will do.
            'environment' => 'production',
            // optional - path to directory your code is in. used for linking stack traces.
            'root' => PATH,
            // optional - the code version. e.g. git commit SHA or release tag
            'code_version' => get_param('rollbar')['code_version']
        );
        \Rollbar\Rollbar::init($config);
    }

    public static function info($message)
    {
        $array = [];
        if (is_array($message)) {
            $array = $message;
            $message = '';
            foreach ($array as $key => $value) {
                $message .= $key.':'.$value.';';
            }
        }
        \Rollbar\Rollbar::log(Level::INFO, $message, $array);
    }

    public static function error($message)
    {
        $array = [];
        if (is_array($message)) {
            $array = $message;
            $message = '';
            foreach ($array as $key => $value) {
                $message .= $key.':'.$value.';';
            }
        }
        \Rollbar\Rollbar::log(Level::ERROR, $message, $array);
    }
}
