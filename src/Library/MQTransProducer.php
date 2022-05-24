<?php

namespace Lwz\HyperfRocketMQ\Library;

use Lwz\HyperfRocketMQ\Library\Exception\AckMessageException;
use Lwz\HyperfRocketMQ\Library\Exception\InvalidArgumentException;
use Lwz\HyperfRocketMQ\Library\Exception\MessageNotExistException;
use Lwz\HyperfRocketMQ\Library\Exception\MQException;
use Lwz\HyperfRocketMQ\Library\Exception\ReceiptHandleErrorException;
use Lwz\HyperfRocketMQ\Library\Exception\TopicNotExistException;
use Lwz\HyperfRocketMQ\Library\Http\HttpClient;
use Lwz\HyperfRocketMQ\Library\Requests\ConsumeMessageRequest;
use Lwz\HyperfRocketMQ\Library\Requests\AckMessageRequest;
use Lwz\HyperfRocketMQ\Library\Responses\AckMessageResponse;
use Lwz\HyperfRocketMQ\Library\Responses\ConsumeMessageResponse;

class MQTransProducer extends MQProducer
{
    private $groupId;

    protected $messageTag;

    public function __construct(HttpClient $client, $instanceId, $topicName, $groupId)
    {
        if (empty($groupId)) {
            throw new InvalidArgumentException(400, "GroupId is null");
        }
        parent::__construct($client, $instanceId, $topicName);
        $this->groupId = $groupId;
    }

    /**
     * consume transaction half message
     *
     * @param $numOfMessages : consume how many messages once, 1~16
     * @param int $waitSeconds : if > 0, means the time(second) the request holden at server if there is no message to consume.
     *                      If <= 0, means the server will response back if there is no message to consume.
     *                      It's value should be 1~30
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws MessageNotExistException if no message exists
     * @throws InvalidArgumentException if the argument is invalid
     * @throws MQException if any other exception happends
     */
    public function consumeHalfMessage($numOfMessages, int $waitSeconds = -1)
    {
        if ($numOfMessages < 0 || $numOfMessages > 16) {
            throw new InvalidArgumentException(400, "numOfMessages should be 1~16");
        }
        if ($waitSeconds > 30) {
            throw new InvalidArgumentException(400, "numOfMessages should less then 30");
        }
        $request = new ConsumeMessageRequest($this->instanceId, $this->topicName, $this->groupId, $numOfMessages, $this->messageTag, $waitSeconds);
        $request->setTrans(Constants::TRANSACTION_POP);
        $response = new ConsumeMessageResponse();
        return $this->client->sendRequest($request, $response);
    }

    /**
     * commit transaction message
     *
     * @param $receiptHandle :
     *            $receiptHandle, which is got from consumeHalfMessage or publishMessage
     *
     * @return AckMessageResponse
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws ReceiptHandleErrorException if the receiptHandle is invalid
     * @throws InvalidArgumentException if the argument is invalid
     * @throws AckMessageException if any message not deleted
     * @throws MQException if any other exception happends
     */
    public function commit($receiptHandle): AckMessageResponse
    {
        $request = new AckMessageRequest($this->instanceId, $this->topicName, $this->groupId, [$receiptHandle]);
        $request->setTrans(Constants::TRANSACTION_COMMIT);
        $response = new AckMessageResponse();
        return $this->client->sendRequest($request, $response);
    }


    /**
     * rollback transaction message
     *
     * @param $receiptHandle :
     *            $receiptHandle, which is got from consumeHalfMessage or publishMessage
     *
     * @return AckMessageResponse
     *
     * @throws TopicNotExistException if queue does not exist
     * @throws ReceiptHandleErrorException if the receiptHandle is invalid
     * @throws InvalidArgumentException if the argument is invalid
     * @throws AckMessageException if any message not deleted
     * @throws MQException if any other exception happends
     */
    public function rollback($receiptHandle): AckMessageResponse
    {
        $request = new AckMessageRequest($this->instanceId, $this->topicName, $this->groupId, [$receiptHandle]);
        $request->setTrans(Constants::TRANSACTION_ROLLBACK);
        $response = new AckMessageResponse();
        return $this->client->sendRequest($request, $response);
    }
}
