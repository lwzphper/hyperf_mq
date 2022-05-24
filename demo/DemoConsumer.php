<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/24 11:15,
 * @LastEditTime: 2022/05/24 11:15
 */
declare(strict_types=1);

use Lwz\HyperfRocketMQ\Annotation\Consumer;
use Lwz\HyperfRocketMQ\Library\Model\Message as RocketMQMessage;
use Lwz\HyperfRocketMQ\Message\ConsumerMessage;

#[Consumer(topic: "Topic_03_test", groupId: "test_test", messageTag: "hyperf_test", name: "DemoConsumer", processNums: 2)]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage(RocketMQMessage $rocketMQMessage)
    {
        $msgTag = $rocketMQMessage->getMessageTag(); // 消息标签
        $msgKey = $rocketMQMessage->getMessageKey(); // 消息唯一标识
        $msgBody = $this->unserialize($rocketMQMessage->getMessageBody()); // 消息体
        $msgId = $rocketMQMessage->getMessageId();

        // todo 消息消费逻辑...
        var_dump('消息成功' . $rocketMQMessage->getMessageBody());
    }
}