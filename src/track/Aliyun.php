<?php


/**
 * 物流查寻
 * https://market.aliyun.com/apimarket/detail/cmapi021863
 */

namespace app\pack\track;

use app\helper\ZCurlAliyun;

class Aliyun extends Base
{
    public static function get($no, $type)
    {
        $cache_key = 'track_aliyun:' . $no . $type;
        $list = get_cache($cache_key);
        if ($list) {
            $list['cache'] = 1;
            return $list;
        }
        $url = "https://wuliu.market.alicloudapi.com/kdi";
        $res = ZCurlAliyun::get($url, [
            'no' => $no,
            'type' => $type,
        ], 'GET'); 
        if ($res['code'] != 0) {
            return $res;
        }
        $find_list = $res['result']['list']??[];
        $new_list = [];
        if (!$find_list) {
            return [];
        }
        foreach ($find_list as $v) {
            $time_arr = explode(' ', $v['time']);
            $new_list[] = [
                'title' => $v['status'] ?? '',
                'status' => self::parseTitle($v['status'] ?? ''),
                'time' => $v['time'],
                'time_arr' => $time_arr,
            ];
        } 
        $status_arr = [
            0 => '快递收件(揽件)',
            1 => '在途中',
            2 => '正在派件',
            3 => '已签收',
            4 => '派送失败',
            5 => '疑难件',
            6 => '退件签收',
        ];
        $list = [];
        $list['status'] = $status_arr[$res['result']['deliverystatus']??''] ?? '';
        $list['no'] = $res['result']['number'];
        $list['title'] = $res['result']['expName']??'';
        $list['site_url'] = $res['result']['expSite']??'';
        $list['site_phone'] = $res['result']['expPhone']??'';
        $list['phone'] = $res['result']['courierPhone']??'';
        $list['take_time'] = $res['result']['takeTime']??'';
        $list['logo'] = $res['result']['logo']??'';
        $list['list'] = $new_list;
        $list['code'] = 0;
        set_cache($cache_key, $list, 300);
        return $list;
    }
}
