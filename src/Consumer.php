<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/17 22:59,
 * @LastEditTime: 2022/5/17 22:59
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ;

use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Process\ProcessManager;
use Lwz\HyperfRocketMQ\Event\AfterConsume;
use Lwz\HyperfRocketMQ\Event\BeforeConsume;
use Lwz\HyperfRocketMQ\Event\FailToConsume;
use Lwz\HyperfRocketMQ\Library\Exception\AckMessageException;
use Lwz\HyperfRocketMQ\Library\Exception\MessageNotExistException;
use Lwz\HyperfRocketMQ\Library\Model\Message as RocketMQMessage;
use Lwz\HyperfRocketMQ\Message\ConsumerMessageInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine;
use \Throwable;

class Consumer extends Builder
{
    /**
     * @throws Throwable
     */
    public function consume(ConsumerMessageInterface $consumerMessage): void
    {
        $poolName = $consumerMessage->getPoolName();
        $config = new Config($poolName);
        $consumer = $this->getClient($config)->getConsumer(
            $config->getInstanceId(), $consumerMessage->getTopic(),
            $consumerMessage->getGroupId(), $consumerMessage->getMessageTag()
        );

        $this->setLogger($consumerMessage->getLogGroup());

        $maxConsumption = $consumerMessage->getMaxConsumption();
        $currentConsumption = 0;

        while (ProcessManager::isRunning()) {
            try {
                // 长轮询消费消息
                // 长轮询表示如果topic没有消息则请求会在服务端挂住3s，3s内如果有消息可以消费则立即返回
                $messages = $consumer->consumeMessage(
                    $consumerMessage->getNumOfMessage(), // 一次最多消费3条(最多可设置为16条)
                    $consumerMessage->getWaitSeconds() // 长轮询时间（最多可设置为30秒）
                );
            } catch (MessageNotExistException $e) {
                continue;
            } catch (Throwable $exception) {
                $this->logger->error((string)$exception);
                throw $exception;
            }

            $receiptHandles = [];
            if ($consumerMessage->getOpenCoroutine()) { // 协程并发消费
                $callback = [];
                foreach ($messages as $key => $message) {
                    $callback[$key] = $this->getCallBack($consumerMessage, $message);
                }
                $receiptHandles = parallel($callback);
            } else { // 同步执行
                foreach ($messages as $message) {
                    $receiptHandles[] = call($this->getCallBack($consumerMessage, $message));
                }
            }

            try {
                $consumer->ackMessage($receiptHandles);
                if ($maxConsumption > 0 && ++$currentConsumption >= $maxConsumption) {
                    break;
                }
            } catch (AckMessageException $exception) {
                // 某些消息的句柄可能超时了会导致确认不成功
                $this->logger->error("ack_error", ['RequestId' => $exception->getRequestId()]);
                foreach ($exception->getAckMessageErrorItems() as $errorItem) {
                    $this->logger->error('ack_error:receipt_handle', [
                        $errorItem->getReceiptHandle(), $errorItem->getErrorCode(), $errorItem->getErrorCode(),
                    ]);
                }
            } catch (Throwable $e) {
                $this->logger->error((string)$e);
                break;
            }
        }
    }

    protected function getCallBack(ConsumerMessageInterface $consumerMessage, RocketMQMessage $message): \Closure
    {
        return function () use ($consumerMessage, $message) {
            try {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeConsume($consumerMessage));
                $consumerMessage->consumeMessage($message);
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterConsume($consumerMessage));
                // 记录消费日志
                if ($consumerMessage->getSaveConsumeLog()) {
                    $this->logger->info('[消息消费成功]', [
                        'message_key' => $message->getMessageKey(),
                        'message_tag' => $message->getMessageTag(),
                        'message_id' => $message->getMessageId(),
                        'payload' => $message->getMessageBody(),
                    ]);
                }
            } catch (\Throwable $throwable) {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new FailToConsume($consumerMessage, $throwable));
                if ($this->container->has(FormatterInterface::class)) {
                    $formatter = $this->container->get(FormatterInterface::class);
                    $this->logger->error($formatter->format($throwable));
                } else {
                    $this->logger->error($throwable->getMessage());
                }
            }
            return $message->getReceiptHandle();
        };
    }
}