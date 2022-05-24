<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/17 17:03,
 * @LastEditTime: 2022/05/17 17:03
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Message;

use Hyperf\Utils\ApplicationContext;
use Lwz\HyperfRocketMQ\Library\Model\Message as RocketMQMessage;
use Lwz\HyperfRocketMQ\Packer\Packer;
use Psr\Container\ContainerInterface;

class ConsumerMessage extends Message implements ConsumerMessageInterface
{
    /**
     * @var ContainerInterface
     */
    public ContainerInterface $container;

    /**
     * 是否记录消费日志
     * @var bool
     */
    protected bool $saveConsumeLog = true;

    /**
     * @var string
     */
    public string $groupId;

    /**
     * filter tag for consumer. If not empty, only consume the message which's messageTag is equal to it.
     * @var string
     */
    public string $messageTag;

    /**
     * consume how many messages once, 1~16
     * @var int
     */
    public int $numOfMessage = 1;

    /**
     * if > 0, means the time(second) the request holden at server if there is no message to consume.
     * If <= 0, means the server will response back if there is no message to consume.
     * It's value should be 1~30
     * @var int|null
     */
    public ?int $waitSeconds = 3;

    /**
     * 进程数量
     * @var int
     */
    public int $processNums = 1;

    /**
     * 是否初始化时启动
     * @var bool
     */
    public bool $enable = true;

    /**
     * 进程最大消费数
     * @var int
     */
    public int $maxConsumption = 0;

    /**
     * 消费消息
     * @param RocketMQMessage $rocketMQMessage
     * @return mixed
     * @author lwz
     */
    public function consumeMessage(RocketMQMessage $rocketMQMessage)
    {
        $msgTag = $rocketMQMessage->getMessageTag(); // 消息标签
        $msgKey = $rocketMQMessage->getMessageKey(); // 消息唯一标识
        $msgBody = $this->unserialize($rocketMQMessage->getMessageBody()); // 消息体
        $msgId = $rocketMQMessage->getMessageId();

        // todo 消费处理
    }

    /**
     * 是否开启协程并发消费
     * @var bool
     */
    public bool $openCoroutine = true;

    public function getGroupId(): string
    {
        return $this->groupId ?? '';
    }

    public function setGroupId(string $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getMessageTag(): ?string
    {
        return $this->messageTag ?? null;
    }

    public function setMessageTag(string $messageTag): self
    {
        $this->messageTag = $messageTag;
        return $this;
    }

    public function getNumOfMessage(): int
    {
        return $this->numOfMessage;
    }

    public function setNumOfMessage(int $num): self
    {
        $this->numOfMessage = $num;
        return $this;
    }

    public function getWaitSeconds(): int
    {
        return $this->waitSeconds;
    }

    public function setWaitSeconds(int $seconds): self
    {
        $this->waitSeconds = $seconds;
        return $this;
    }

    public function getProcessNums(): int
    {
        return $this->processNums;
    }

    public function setProcessNums(int $num): self
    {
        $this->processNums = $num;
        return $this;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;
        return $this;
    }

    public function getMaxConsumption(): int
    {
        return $this->maxConsumption;
    }

    public function setMaxConsumption(int $num): self
    {
        $this->maxConsumption = $num;
        return $this;
    }

    public function getOpenCoroutine(): bool
    {
        return $this->openCoroutine;
    }

    public function getSaveConsumeLog(): bool
    {
        return $this->saveConsumeLog;
    }

    public function setSaveConsumeLog(bool $isSaveLog): self
    {
        $this->saveConsumeLog = $isSaveLog;
        return $this;
    }

    public function setOpenCoroutine(bool $isOpen): self
    {
        $this->openCoroutine = $isOpen;
        return $this;
    }


    public function unserialize(string $data)
    {
        $container = ApplicationContext::getContainer();
        $packer = $container->get(Packer::class);

        return $packer->unpack($data);
    }
}