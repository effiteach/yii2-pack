<?php

namespace app\pack\order_num;

class OrderNum
{
    /**
     * $order_num = get_pack('order_num.OrderNum', 'create');
     */
    public static function create()
    {
        $workerId = 1; // 实际应用中，可以根据节点设置工作ID
        $datacenterId = 1; // 数据中心ID
        $snowflake = new \app\pack\order_num\Snowflake($workerId, $datacenterId);
        return $snowflake->nextId();
    }
}
