<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/17 17:07,
 * @LastEditTime: 2022/05/17 17:07
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Message;


use Hyperf\Amqp\Exception\MessageException;

abstract class Message implements MessageInterface
{
    protected string $poolName = 'default';

    protected string $topic = '';

    /**
     * 日志分组
     * @var string
     */
    protected string $logGroup = 'default';

    public function getPoolName(): string
    {
        return $this->poolName;
    }

    public function setPoolName(string $poolName): self
    {
        $this->poolName = $poolName;
        return $this;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;
        return $this;
    }

    public function getLogGroup(): string
    {
        return $this->logGroup;
    }

    public function setLogGroup(string $logGroup): self
    {
        $this->logGroup = $logGroup;
        return $this;
    }


    public function serialize(): string
    {
        throw new MessageException('You have to overwrite serialize() method.');
    }

    public function unserialize(string $data)
    {
        throw new MessageException('You have to overwrite unserialize() method.');
    }
}