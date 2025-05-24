<?php

namespace app\pack\notice;

use app\models\Notice as NoticeModel;
use Yii;

class Notice
{
    /**
     * 添加系统消息
     * get_pack('notice.Notice', 'addSys',[ $title,$body,$data = []]);
     */
    public static function addSys($title, $body = '', $data = [])
    {
        $from_user_id = 0;
        $to_user_id = 0;
        $type = 'system_notice';
        return self::add($from_user_id, $to_user_id, $type, $title, $body, $data);
    }
    /**
     * 添加到消息队列
     * 1.记录到消息数据表
     * 2.调用pusher发消息
     * get_pack('notice.Notice', 'add',[$from_user_id,$to_user_id ,$type, $title,$body,$data = []]);
     */
    public static function add($from_user_id, $to_user_id, $type, $title, $body = '', $data = [])
    {
        global $notice_id;
        $data['user_id'] = $to_user_id;
        $data['title'] = $title;
        $data['body'] = $body;
        if (!$title) {
            yii_error(t("Notice title is empty"));
            return;
        }
        try {
            $model = new NoticeModel();
            $model->from_user_id = $from_user_id;
            $model->to_user_id = $to_user_id;
            $model->title = $title;
            $model->content = $body;
            $model->type = $type;
            $model->save();
            $notice_id = $model->id;
        } catch (\Throwable $e) {
            yii_error($e->getMessage());
            return false;
        }
        $cls = self::init();
        $data['notice_id'] = $notice_id;
        $cls::send($data);
    }
    /**
     * get_pack('notice.Notice', 'js');
     */
    public static function js()
    {
        $cls = self::init();
        if ($cls) {
            return $cls::js();
        }
    }
    public static function init()
    {
        $drive = get_param("notice")['drive'];
        if (!$drive) {
            return;
        }
        $class = "\\app\\pack\\notice\\" . ucfirst($drive);
        if (class_exists($class)) {
            return $class;
        }
    }
    /**
     * 发送消息
     */
    public static function send($data =  [])
    {
        $cls = self::init();
        if ($cls) {
            return $cls::send($data);
        }
    }
}
