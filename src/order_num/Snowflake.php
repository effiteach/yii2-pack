<?php

namespace app\pack\order_num;

use Yii;

class Snowflake
{
    private $workerId; // 工作节点ID
    private $datacenterId; // 数据中心ID
    private $sequence; // 每毫秒的序列号
    private $lastTimestamp; // 上一时间戳

    public const SEQUENCE_BITS = 12; // 序列号所占的位数
    public const WORKER_ID_BITS = 5; // 工作节点ID所占的位数
    public const DATACENTER_ID_BITS = 5; // 数据中心ID所占的位数

    public const MAX_WORKER_ID = -1 ^ (-1 << self::WORKER_ID_BITS); // 最大工作节点ID
    public const MAX_DATACENTER_ID = -1 ^ (-1 << self::DATACENTER_ID_BITS); // 最大数据中心ID
    public const SEQUENCE_MASK = -1 ^ (-1 << self::SEQUENCE_BITS); // 最大序列号

    public function __construct($workerId, $datacenterId)
    {
        if ($workerId < 0 || $workerId > self::MAX_WORKER_ID) {
            throw new InvalidArgumentException("Worker ID must be between 0 and " . self::MAX_WORKER_ID);
        }

        if ($datacenterId < 0 || $datacenterId > self::MAX_DATACENTER_ID) {
            throw new InvalidArgumentException("Datacenter ID must be between 0 and " . self::MAX_DATACENTER_ID);
        }

        $this->workerId = $workerId;
        $this->datacenterId = $datacenterId;
        $this->sequence = 0;
        $this->lastTimestamp = -1;
    }

    public function nextId()
    {
        $lockKey = 'snowflake_lock:' . $this->workerId; // 锁的唯一键
        $lockTimeout = 5; // 锁的超时时间（秒）

        // 尝试获取锁
        if (!Yii::$app->cache->add($lockKey, 1, $lockTimeout)) {
            // 如果锁已经存在，表示另一个进程正在生成ID，直接返回
            return null;
        }

        try {
            $timestamp = $this->currentTimestamp();

            if ($timestamp === $this->lastTimestamp) {
                // 在同一毫秒内，生成序列号
                $this->sequence = ($this->sequence + 1) & self::SEQUENCE_MASK;

                if ($this->sequence === 0) {
                    // 如果序列号溢出，等待下一毫秒
                    $timestamp = $this->waitNextMillis($this->lastTimestamp);
                }
            } else {
                // 不在同一毫秒，则序列号重置
                $this->sequence = 0;
            }

            $this->lastTimestamp = $timestamp;

            // 组装ID
            return (($timestamp << (self::DATACENTER_ID_BITS + self::WORKER_ID_BITS + self::SEQUENCE_BITS)) |
                ($this->datacenterId << (self::WORKER_ID_BITS + self::SEQUENCE_BITS)) |
                ($this->workerId << self::SEQUENCE_BITS) |
                $this->sequence);
        } finally {
            // 释放锁
            Yii::$app->cache->delete($lockKey);
        }
    }

    private function currentTimestamp()
    {
        return (int) (microtime(true) * 1000); // 当前时间戳（毫秒）
    }

    private function waitNextMillis($lastTimestamp)
    {
        $timestamp = $this->currentTimestamp();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->currentTimestamp();
        }
        return $timestamp;
    }
}
