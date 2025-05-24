<?php


/**
 * 本类依赖 thefunpower/vue
 */

namespace app\pack\charts;

class Echarts
{
    public static $data;
    public static function set($ele_id, $options = [])
    {
        if (!isset($options['yAxis'])) {
            $options['yAxis'] = "js:{}";
        }
        global $vue;
        $key = "echart_" . $ele_id;
        $vue->data($key, "");
        $echats = "_this." . $key . " = echarts.init(_this.\$refs.$ele_id);";
        self::$data[$key] = $options;
        $vue->mounted("init_echarts_" . $ele_id, " 
            _this.\$nextTick(function(){"
            . $echats . "
            });
        ");
    }
    public static function init()
    {
        if (self::$data) {
            $js = '';
            foreach (self::$data as $k => $v) {
                $js .= "_this." . $k . ".clear();";
                $js .= "_this." . $k . ".setOption(" . php_to_js($v) . ");\n";
            }
            return $js;
        }
    }
}
