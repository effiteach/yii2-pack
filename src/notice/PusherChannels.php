<?php

/**
 * pusher.com
 */

namespace app\pack\notice;

use app\helper\Action;
use Yii;

class PusherChannels
{
    public static $config;
    public static function getConfig()
    {
        self::$config = get_param("notice")['pusherChannels'];
    }
    /**
     * get_pack('notice.Pusher', 'js');
     */
    public static function js()
    {
        self::getConfig();
        add_js_file("https://js.pusher.com/8.2.0/pusher.min.js", 'pusher');
        $action = '';
        Action::do("notice_js", $action);
        add_js("
            Pusher.logToConsole = " . (self::$config['debug'] ?: 0) . ";
            var pusher = new Pusher('" . self::$config['key'] . "', {
                cluster: '" . self::$config['cluster'] . "'
            });
            var channel = pusher.subscribe('" . self::$config['channel_name'] . "');
            channel.bind('" . self::$config['event_name'] . "', function(data) {
                console.log(data);
                " . $action . "
            });
        ", 'pusher');
    }
    /**
     * get_pack('notice.Pusher', 'send', [ [] ]);
     */
    public static function send($data = [])
    {
        self::getConfig();
        $obj = self::init();
        $res = $obj->trigger(self::$config['channel_name'], self::$config['event_name'], $data);
    }
    public static function init()
    {
        self::getConfig();
        $options = array(
            'cluster' => self::$config['cluster'],
            'useTLS' => false,
        );
        return new \Pusher\Pusher(
            self::$config['key'],
            self::$config['secret'],
            self::$config['app_id'],
            $options
        );

    }
}
