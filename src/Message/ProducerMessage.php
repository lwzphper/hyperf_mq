<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/13 23:19,
 * @LastEditTime: 2022/5/13 23:19
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Message;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Lwz\HyperfRocketMQ\Constants\MqConstant;
use Lwz\HyperfRocketMQ\Exception\RocketMQException;
use Lwz\HyperfRocketMQ\Model\MqStatusLog;
use Lwz\HyperfRocketMQ\Packer\Packer;

class ProducerMessage extends Message implements ProducerMessageInterface
{
    protected string $messageKey = '';

    protected string $messageTag = '';

    protected string|array $payload = '';

    /**
     * @var string
     */
    protected string $dbConnection = 'default';

    protected bool $hasSaveStatusLog = false;

    /**
     * 是否记录生产日志
     * @var bool
     */
    protected bool $saveProduceLog = true;

    /**
     * 投递时间（10位时间戳）
     * @var int|null
     */
    protected ?int $deliverTime = null;

    public function getMessageKey(): string
    {
        if (!$this->messageKey) {
            $this->setMessageKey(session_create_id('rocketmq'));
        }
        return $this->messageKey;
    }

    public function setMessageKey(string $messageKey): self
    {
        $this->messageKey = $messageKey;
        return $this;
    }

    public function getMessageTag(): string
    {
        return $this->messageTag;
    }

    public function setMessageTag(string $messageTag): self
    {
        $this->messageTag = $messageTag;
        return $this;
    }

    public function setPayload($data): self
    {
        $this->payload = $data;
        return $this;
    }

    public function payload(): string
    {
        return $this->serialize();
    }

    public function getSaveProduceLog(): bool
    {
        return $this->saveProduceLog;
    }

    public function setSaveProduceLog(bool $isSaveLog): self
    {
        $this->saveProduceLog = $isSaveLog;
        return $this;
    }

    public function saveMessageStatus()
    {
        if (!$this->payload()) {
            throw new RocketMQException('请设置payload');
        }

        (new MqStatusLog)->setConnection($this->getDbConnection())
            ->insert([
                'status' => MqConstant::PRODUCE_STATUS_WAIT,
                'message_key' => $this->getMessageKey(),
                'mq_info' => Json::encode($this->getProduceInfo()),
            ]);
        $this->hasSaveStatusLog = true;
    }

    public function removeMessageStatus()
    {
        $this->hasSaveStatusLog && (new MqStatusLog)->setConnection($this->getDbConnection())
            ->where('message_key', $this->getMessageKey())
            ->delete();
    }

    public function getDbConnection(): string
    {
        return $this->dbConnection;
    }

    public function setDbConnection(string $connection): self
    {
        $this->dbConnection = $connection;
        return $this;
    }

    /**
     * 获取生成的消息信息
     * @param ProducerMessageInterface $producerMessage
     * @return array
     */
    public function getProduceInfo(): array
    {
        return [
            'pool' => $this->getPoolName(),
            'topic' => $this->getTopic(),
            'message_key' => $this->getMessageKey(),
            'message_tag' => $this->getMessageTag(),
            'payload' => $this->payload(),
        ];
    }

    /**
     * Serialize the message body to a string.
     */
    public function serialize(): string
    {
        $packer = ApplicationContext::getContainer()->get(Packer::class);
        return $packer->pack($this->payload);
    }

    public function getDeliverTime(): ?int
    {
        return $this->deliverTime ? $this->deliverTime * 1000 : null;
    }

    public function setDeliverTime(int $timestamp): self
    {
        $this->deliverTime = $timestamp;
        return $this;
    }
}