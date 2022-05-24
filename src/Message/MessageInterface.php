<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/17 17:07,
 * @LastEditTime: 2022/05/17 17:07
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Message;


interface MessageInterface
{
    public function getPoolName(): string;

    public function setPoolName(string $poolName);

    public function getTopic(): string;

    public function setTopic(string $topic);

    public function getLogGroup(): string;

    public function setLogGroup(string $logGroup);

    /**
     * Serialize the message body to a string.
     */
    public function serialize(): string;

    /**
     * Unserialize the message body.
     */
    public function unserialize(string $data);
}