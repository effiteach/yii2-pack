<?php

namespace app\pack\map;

class Tencent
{
    /**
     * 腾讯地图
     * https://lbs.qq.com/dev/console/key/setting
     * @param  [type] $address [description]
     * @return [type]          [description]
     */
    public static function getLat($address, $lat = true)
    {
        $key = get_param("map")['tencent'];
        $address = urlencode($address);
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?address=" . $address . "&key=" . $key;
        $res = self::get_request($url);
        $res = json_decode($res, true);
        if ($lat === true) {
            $t = $res['result']['location'];
            $t['data'] = $res;
            return $t;
        }
        return $res;
    }
    /**
     * 根据坐标点显示地址 纬度 经度
     * @param  [type]  $lat  [description]
     * @param  [type]  $lng  [description]
     * @param  boolean $full [description]
     * @return [type]        [description]
     */
    public static function getAddress($lat, $lng, $full = false)
    {
        $key = get_param("map")['tencent'];
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=" . $lat . ',' . $lng . "&key=" . $key;
        $res = self::get_request($url);
        $res = json_decode($res, true);
        if ($full === false) {
            return ['data' => $res['result']['address']];
        }
        return $res;
    }
}
