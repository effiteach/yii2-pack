<?php

namespace app\pack\track;

class Base
{
    public static function parseTitle($remark = '')
    {
        if (!$remark) {
            return;
        }
        $status = '';
        if (
            strpos($remark, '签收') !== false || strpos($remark, '代收') !== false
            || strpos($remark, '代签收') !== false
            || strpos($remark, '快件已放在') !== false
            || strpos($remark, '已派送成功') !== false
            || strpos($remark, '已派送至本人') !== false
            || strpos($remark, '经客户同意') !== false

        ) {
            $status = '已签收';
        } else if (strpos($remark, '派件') !== false || strpos($remark, '派送') !== false) {
            $status = '派送中';
        } else if (
            strpos($remark, '发往') !== false || strpos($remark, '离开') !== false  || strpos($remark, '到达') !== false ||
            strpos($remark, '发件') !== false || strpos($remark, '到件') !== false || strpos($remark, '运输中') !== false
        ) {
            $status = '运输中';
        } else if (
            strpos($remark, '揽收') !== false || strpos($remark, '收取') !== false ||
            strpos($remark, '收件') !== false
        ) {
            $status = '已揽收';
        } else if (strpos($remark, '取消') !== false) {
            $status = '已取消';
        }
        return $status;
    }
}
