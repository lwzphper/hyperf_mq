<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/11 23:09,
 * @LastEditTime: 2022/5/11 23:09
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ;


use Hyperf\Di\Annotation\AnnotationCollector;
use Lwz\HyperfRocketMQ\Event\AfterProduce;
use Lwz\HyperfRocketMQ\Library\Model\TopicMessage;
use Lwz\HyperfRocketMQ\Library\MQProducer;
use Lwz\HyperfRocketMQ\Message\ProducerMessageInterface;

class Producer extends Builder
{
    public function produce(ProducerMessageInterface $producerMessage): bool
    {
        $this->injectMessageProperty($producerMessage);

        $poolName = $producerMessage->getPoolName();
        $config = new Config($poolName);
        $result = $this->checkIsProduceSuccess($this->publishMessage($config, $producerMessage));

        if ($result) {
            $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterProduce($producerMessage));
            // 如果记录生产状态日志，消费成功删除日志
            $producerMessage->removeMessageStatus();

            // 记录日志
            $this->setLogger($producerMessage->getLogGroup());
            if ($producerMessage->getSaveProduceLog()) {
                $this->logger->info('[消息生成成功]', $producerMessage->getProduceInfo());
            }
        }

        return $result;
    }

    protected function publishMessage(Config $config, ProducerMessageInterface $message): TopicMessage
    {
        $producer = $this->getProducer($config, $message);

        $publishMessage = new TopicMessage($message->payload());
        $message->getMessageTag() && $publishMessage->setMessageTag($message->getMessageTag());
        if ($timeInMillis = $message->getDeliverTime()) {
            $publishMessage->setStartDeliverTime($timeInMillis);
        }

        return $producer->publishMessage($publishMessage);
    }

    /**
     * 判断是否投递成功
     * @param TopicMessage $publishRet
     * @return bool
     */
    private function checkIsProduceSuccess(TopicMessage $publishRet): bool
    {
        // 如果返回了 message id ，则视为投递成功（不考虑，MQ存储缓存丢失情况）
        return isset($publishRet->messageId) && !empty($publishRet->messageId);
    }

    private function getProducer(Config $config, ProducerMessageInterface $producerMessage): MQProducer
    {
        return $this->getClient($config)->getProducer($config->getInstanceId(), $producerMessage->getTopic());
    }

    private function injectMessageProperty(ProducerMessageInterface $producerMessage)
    {
        if (class_exists(AnnotationCollector::class)) {
            /** @var \Lwz\HyperfRocketMQ\Annotation\Producer $annotation */
            $annotation = AnnotationCollector::getClassAnnotation(get_class($producerMessage), Annotation\Producer::class);
            if ($annotation) {
                $annotation->topic && $producerMessage->setTopic($annotation->topic);
                $annotation->messageTag && $producerMessage->setMessageTag($annotation->messageTag);
                $annotation->messageKey && $producerMessage->setMessageKey($annotation->messageKey);
            }
        }
    }
}