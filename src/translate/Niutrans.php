<?php

/**
 * 小牛翻译
 * 购买，有免费额度
 * https://niutrans.com/documents/contents/trans_text#accessMode
 */

namespace app\pack\translate;

use app\helper\Curl;
use Yii;

class Niutrans
{
    public $convert = [
        'zh-cn' => 'zh',
    ];
    public function translate($SourceText, $Source = 'zh', $Target = 'en')
    {
        $cacheKey = "Translate.Niutrans." . md5($SourceText . $Source . $Target);
        $result = Yii::$app->cache->get($cacheKey);
        if ($result) {
            return $result;
        }
        $url = "https://api.niutrans.com/NiuTransServer/translation";
        $Source = $this->convert[$Source] ?? $Source;
        $Target = $this->convert[$Target] ?? $Target;
        $data = [
            'src_text' => $SourceText,
            'from' => $Source,
            'to' => $Target,
            'apikey' => Yii::$app->params['niutrans_text_secret_key'],
        ];
        $url = $url . '?' . http_build_query($data);
        $res = Curl::get($url);
        $res = json_decode($res, true);
        $tgt_text = $res['tgt_text'] ?? '';
        if ($tgt_text) {
            Yii::$app->cache->set($cacheKey, $tgt_text);
            return $tgt_text;
        }
    }

}
