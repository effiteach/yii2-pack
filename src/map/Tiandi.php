<?php

namespace app\pack\map;

use app\helper\Curl;

/**
 * 天地图
 * http://lbs.tianditu.gov.cn/server/search2.html
 */
class Tiandi
{
    /**
     * 服务器端
     */
    public static function get_tk()
    {
        return get_param("map")['tianditu']['server_key'];
    }
    /**
     * 浏览器端
     */
    public static function get_tk_sever()
    {
        return get_param("map")['tianditu']['js_key'];
    }

    public static function get($url)
    {
        $body = Curl::get($url);
        return json_decode($body, true);
    }
    /**
     * 根据lat lng取地址
     */
    public static function getAddress($lat, $lng)
    {
        $url = "http://api.tianditu.gov.cn/geocoder?postStr={'lon':" . $lng . ",'lat':" . $lat . ",'ver':1}&type=geocode&tk=" . self::get_tk();
        $data = self::get($url);
        if ($data['status'] == 0) {
            $res = $data['result'];
            $list = [];
            $list['address'] = $res['formatted_address'];
            $a = $res['addressComponent'];
            $list['parse'] = [
                'nation' => $a['nation'],
                'province' => $a['province'],
                'county' => $a['county'],
                'address' => $a['address'],
            ];
            return $list;
        }
    }
    /**
     * 根据地址取lat lng
     */
    public static function getLat($address, $convert = 'wgs84_gcj02')
    {
        $url = 'http://api.tianditu.gov.cn/geocoder?ds={"keyWord":"' . $address . '"}&tk=' . self::get_tk();
        $data = self::get($url);
        if ($data['status'] == 0) {
            $lat = $data['location']['lat'];
            $lng = $data['location']['lon'];
            return Convert::$convert($lat, $lng);
        }
    }
}
