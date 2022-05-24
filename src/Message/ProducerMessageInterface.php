<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/13 23:27,
 * @LastEditTime: 2022/5/13 23:27
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Message;


interface ProducerMessageInterface extends MessageInterface
{

    public function setPayload($data);

    public function payload(): string;

    public function getMessageKey(): string;

    public function setMessageKey(string $messageKey);

    public function getMessageTag(): string;

    public function setMessageTag(string $messageTag);

    public function getDeliverTime(): ?int;

    public function setDeliverTime(int $timestamp);

    public function getSaveProduceLog(): bool;

    public function setSaveProduceLog(bool $isSaveLog);

    public function getProduceInfo();

    public function saveMessageStatus();

    public function removeMessageStatus();

    public function getDbConnection(): string;

    public function setDbConnection(string $connection);
}