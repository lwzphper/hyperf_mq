<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/13 22:28,
 * @LastEditTime: 2022/5/13 22:28
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ;


use Lwz\HyperfRocketMQ\Exception\RocketMQException;

class Config
{
    protected string $host;

    protected string $accessKey;

    protected string $secretKey;

    protected string $instanceId;

    protected int $minConnections = 10;

    protected int $maxConnections = 100;

    protected float $connectTimeout = 3.0;

    protected float $waitTimeout = 30.0;

    protected int $heartbeat = -1;

    protected float $maxIdleTime = 60.0;

    public function __construct(string $pool = 'default')
    {
        $data = config('rocketmq.' . $pool);
        if (empty($data)) {
            throw new RocketMQException($pool . ' config info error');
        }

        // 必填配置
        $this->setHost($data['host']);
        $this->setAccessKey($data['access_key']);
        $this->setSecretKey($data['secret_key']);
        $this->setInstanceId($data['instance_id']);

        $poolData = $data['pool'];
        isset($poolData['min_connections']) && $this->setMinConnections($poolData['min_connections']);
        isset($poolData['max_connections']) && $this->setMaxConnection($poolData['max_connections']);
        isset($poolData['connect_timeout']) && $this->setConnectTimeout($poolData['connect_timeout']);
        isset($poolData['wait_timeout']) && $this->setWaitTimeout($poolData['wait_timeout']);
        isset($poolData['heartbeat']) && $this->setHeartBeat($poolData['heartbeat']);
        isset($poolData['max_idle_time']) && $this->setMaxIdleTime($poolData['max_idle_time']);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    public function getMinConnections(): int
    {
        return $this->minConnections;
    }

    public function getMaxConnection(): int
    {
        return $this->maxConnections;
    }

    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    public function getWaitTimeout(): float
    {
        return $this->waitTimeout;
    }

    public function getHeartBeat(): int
    {
        return $this->heartbeat;
    }

    public function getMaxIdleTime(): float
    {
        return $this->maxIdleTime;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function setAccessKey(string $accessKey): void
    {
        $this->accessKey = $accessKey;
    }

    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    public function setInstanceId(string $instaceId): void
    {
        $this->instanceId = $instaceId;
    }

    public function setMinConnections(int $minConnections): void
    {
        $this->minConnections = $minConnections;
    }

    public function setMaxConnection(int $maxConnections): void
    {
        $this->maxConnections = $maxConnections;
    }

    public function setConnectTimeout(float $connectTimeout): void
    {
        $this->connectTimeout = $connectTimeout;
    }

    public function setWaitTimeout(float $waitTimeout): void
    {
        $this->waitTimeout = $waitTimeout;
    }

    public function setHeartBeat(int $heartbeat): void
    {
        $this->heartbeat = $heartbeat;
    }

    public function setMaxIdleTime(float $maxIdleTime): void
    {
        $this->maxIdleTime = $maxIdleTime;
    }
}