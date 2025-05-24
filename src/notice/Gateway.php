<?php

/**
 * https://www.workerman.net/doc/gateway-worker/
 */

namespace app\pack\notice;

use app\helper\Action;
use Yii;
use app\helper\Env;

class Gateway
{
    /**
     * 发送消息
     *
     * get_ability('notice.Gateway', 'send',[[
     *  'user_id'=>1,
     *  'content'=>'test',
     *  'type'=>'friend',
     * ]]);
     */
    public static function send($data)
    {
        /**
         * https://github.com/walkor/GatewayClient
         */
        \GatewayClient\Gateway::$registerAddress = '127.0.0.1:1238';
        \GatewayClient\Gateway::sendToAll(json_encode($data));
    }
    /**
     * get_pack('notice.Gateway', 'js');
     */
    public static function js()
    {
        $sockect_url = get_param("notice")['Gateway']['sockect_url'] ?? '';
        add_js_file("/js/reconnecting-websocket.js", 'reconnecting-websocket');
        $token = \app\helper\Aes::encode([
            'user_id' => Yii::$app->user->id,
            'time' => time()
        ]);
        $sockect_url = $sockect_url . '?token=' . urlencode($token);
        $user_id = Yii::$app->user->id;
        $guest = get_cookie("guest");
        if (!$guest) {
            $guest =  md5(uniqid(rand(), true));
            set_cookie('guest', $guest, time() + 86400 * 365 * 10);
        }
        $ip = Env::getIp();
        $message = '';
        Action::do("notice.onmessage", $message);
        $url = Yii::$app->request->url;
        $controller = Env::getRequest();
        $param_value = Env::get('id');
        add_js(" 
            const ws = new ReconnectingWebSocket('" . $sockect_url . "');  
            ws.addEventListener('open', function (event) {  
                ws.send(JSON.stringify({ 
                    event: 'connect',
                    message: '', 
                    url: '" . $url . "',
                    site: '',  
                    user_id: '" . $user_id . "',  
                    gid: '" . $guest . "',   
                    ip: '" . $ip . "',  
                    controller: '" . $controller . "',  
                    param_value: '" . $param_value . "',  
                }));                 
            });  
            ws.addEventListener('error', function (event) {
                console.log('WebSocket error observed');
            });
            ws.addEventListener('close', function (event) {
                console.log('WebSocket closed');
            }); 
            ws.onmessage = function (e) { 
                let message = e.data; 
                try {
                    message = JSON.parse(message); 
                    if (message.type === 'reload') {
                        let method = message.method;
                        for(let i of method){
                            app[i]();
                        } 
                    }
                    " . $message . "
                }catch(err) {
                    
                } 
            };
        ", "websocket-js");
    }
}
