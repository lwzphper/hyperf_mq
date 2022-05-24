<?php
namespace Lwz\HyperfRocketMQ\Library;

use Lwz\HyperfRocketMQ\Library\Exception\AckMessageException;
use Lwz\HyperfRocketMQ\Library\Exception\InvalidArgumentException;
use Lwz\HyperfRocketMQ\Library\Exception\MessageNotExistException;
use Lwz\HyperfRocketMQ\Library\Exception\MQException;
use Lwz\HyperfRocketMQ\Library\Exception\ReceiptHandleErrorException;
use Lwz\HyperfRocketMQ\Library\Exception\TopicNotExistException;
use Lwz\HyperfRocketMQ\Library\Http\HttpClient;
use Lwz\HyperfRocketMQ\Library\Model\Message;
use Lwz\HyperfRocketMQ\Library\Requests\AckMessageRequest;
use Lwz\HyperfRocketMQ\Library\Requests\ConsumeMessageRequest;
use Lwz\HyperfRocketMQ\Library\Responses\AckMessageResponse;
use Lwz\HyperfRocketMQ\Library\Responses\ConsumeMessageResponse;

class MQConsumer
{
    private $instanceId;
    private $topicName;
    private $consumer;
    private $messageTag;
    private $client;


    /**
     * MQConsumer constructor.
     * @param HttpClient $client
     * @param null $instanceId
     * @param $topicName
     * @param $consumer
     * @param null $messageTag
     */
    public function __construct(HttpClient $client, $instanceId, $topicName, $consumer, $messageTag = null)
    {
        if (empty($topicName)) {
            throw new InvalidArgumentException(400, "TopicName is null");
        }
        if (empty($consumer)) {
            throw new InvalidArgumentException(400, "TopicName is null");
        }

        $this->instanceId = $instanceId;
        $this->topicName = $topicName;
        $this->consumer = $consumer;
        $this->messageTag = $messageTag;
        $this->client = $client;
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }

    public function getTopicName()
    {
        return $this->topicName;
    }

    public function getConsumer()
    {
        return $this->consumer;
    }

    public function getMessageTag()
    {
        return $this->messageTag;
    }


    /**
     * consume message
     *
     * @param $numOfMessages: consume how many messages once, 1~16
     * @param int $waitSeconds: if > 0, means the time(second) the request holden at server if there is no message to consume.
     *                      If <= 0, means the server will response back if there is no message to consume.
     *                      It's value should be 1~30
     *
     * @return array<Message>
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws MessageNotExistException if no message exists
     * @throws InvalidArgumentException if the argument is invalid
     * @throws MQException if any other exception happends
     */
    public function consumeMessage($numOfMessages, int $waitSeconds = -1): array
    {
        if ($numOfMessages < 0 || $numOfMessages > 16) {
            throw new InvalidArgumentException(400, "numOfMessages should be 1~16");
        }
        if ($waitSeconds > 30) {
            throw new InvalidArgumentException(400, "numOfMessages should less then 30");
        }
        $request = new ConsumeMessageRequest($this->instanceId, $this->topicName, $this->consumer, $numOfMessages, $this->messageTag, $waitSeconds);
        $response = new ConsumeMessageResponse();
        return $this->client->sendRequest($request, $response);
    }

    /**
     * consume message orderly
     *
     * Next messages will be consumed if all of same shard are acked. Otherwise, same messages will be consumed again after NextConsumeTime.
     *
     * Attention: the topic should be order topic created at console, if not, mq could not keep the order feature.
     *
     * This interface is suitable for globally order and partitionally order messages, and could be used in multi-thread scenes.
     *
     * @param $numOfMessages: consume how many messages once, 1~16
     * @param int $waitSeconds: if > 0, means the time(second) the request holden at server if there is no message to consume.
     *                      If <= 0, means the server will response back if there is no message to consume.
     *                      It's value should be 1~30
     *
     * @return Message may contains several shard's messages, the messages of one shard are ordered.
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws MessageNotExistException if no message exists
     * @throws InvalidArgumentException if the argument is invalid
     * @throws MQException if any other exception happends
     */
    public function consumeMessageOrderly($numOfMessages, int $waitSeconds = -1): Message
    {
        if ($numOfMessages < 0 || $numOfMessages > 16) {
            throw new InvalidArgumentException(400, "numOfMessages should be 1~16");
        }
        if ($waitSeconds > 30) {
            throw new InvalidArgumentException(400, "numOfMessages should less then 30");
        }
        $request = new ConsumeMessageRequest($this->instanceId, $this->topicName, $this->consumer, $numOfMessages, $this->messageTag, $waitSeconds);
        $request->setTrans(Constants::TRANSACTION_ORDER);
        $response = new ConsumeMessageResponse();
        return $this->client->sendRequest($request, $response);
    }

    /**
     * ack message
     *
     * @param $receiptHandles:
     *            array of $receiptHandle, which is got from consumeMessage
     *
     * @return AckMessageResponse
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws ReceiptHandleErrorException if the receiptHandle is invalid
     * @throws InvalidArgumentException if the argument is invalid
     * @throws AckMessageException if any message not deleted
     * @throws MQException if any other exception happends
     */
    public function ackMessage($receiptHandles): ?AckMessageResponse
    {
        $request = new AckMessageRequest($this->instanceId, $this->topicName, $this->consumer, $receiptHandles);
        $response = new AckMessageResponse();
        return $this->client->sendRequest($request, $response);
    }
}
