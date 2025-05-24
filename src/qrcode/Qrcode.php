<?php

namespace app\pack\qrcode;

use app\helper\Color as ColorHelper;
use app\helper\Dir;
use app\helper\File;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Writer\PngWriter;
use Yii;

class Qrcode
{
    /**
     * $res = get_pack('qrcode.qrcode', 'get', [[
     *    'content' => $content,
     * ]]);
     */
    public static function get($arr)
    {
        $content = $arr['content'];
        $header = $arr['header'] ?? false;
        $label = $arr['label'] ?? [];
        $logo = $arr['logo'] ?? [];
        $width = $arr['width'] ?? 300;
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($content)
            ->encoding(new Encoding('UTF-8'))
            ->size($width)
            ->margin(5)
            ->validateResult(false);
        if ($label) {
            $c = $label['color'] ?? '#000000';
            $c = ColorHelper::hexToRgb($c);
            $top = $label['top'] ?? 0;
            $label = $result->labelText($label['text'])
                ->labelFont(new NotoSans(20))
                ->labelTextColor(new Color($c[0], $c[1], $c[2]))
                ->labelMargin(new Margin($top, 0, 0, 0))
                ->labelAlignment(LabelAlignment::Center);
        }
        if ($logo) {
            $result = $result->logoPath($logo['url'])
                ->logoResizeToWidth($logo['width'])
                ->logoResizeToHeight($logo['height'] ?? $logo['width'])
                ->logoPunchoutBackground(true);
        }
        $result = $result->build();
        if ($header) {
            header('Content-Type: ' . $result->getMimeType());
            echo $result->getString();
            exit;
        } else {
            $md5 = md5($content);
            $md = substr($md5, 0, 8) . '/' . substr($md5, 8);
            $url = '/uploads/qrcode/' . $md . '.png';
            $file = Yii::getAlias('@webroot' . $url);
            $dir = File::getDir($file);
            Dir::create($dir);
            if (!file_exists($file)) {
                $result->saveToFile($file);
            }
            return $url;
        }
    }
}
