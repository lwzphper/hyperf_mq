<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/17 17:03,
 * @LastEditTime: 2022/05/17 17:03
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Message;

use Lwz\HyperfRocketMQ\Library\Model\Message as RocketMQMessage;

interface ConsumerMessageInterface extends MessageInterface
{
    public function consumeMessage(RocketMQMessage $rocketMQMessage);

    public function getGroupId(): string;

    public function setGroupId(string $groupId);

    public function getMessageTag(): ?string;

    public function setMessageTag(string $messageTag);

    public function getNumOfMessage(): int;

    public function setNumOfMessage(int $num);

    public function getWaitSeconds(): int;

    public function setWaitSeconds(int $seconds);

    public function getProcessNums(): int;

    public function setProcessNums(int $num);

    public function isEnable(): bool;

    public function setEnable(bool $enable);

    public function getMaxConsumption(): int;

    public function setMaxConsumption(int $num);

    public function getOpenCoroutine(): bool;

    public function setOpenCoroutine(bool $isOpen);

    public function getSaveConsumeLog(): bool;

    public function setSaveConsumeLog(bool $isSaveLog);
}