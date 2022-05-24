<?php
namespace Lwz\HyperfRocketMQ\Library;

use Lwz\HyperfRocketMQ\Library\Exception\InvalidArgumentException;
use Lwz\HyperfRocketMQ\Library\Http\HttpClient;
use Lwz\HyperfRocketMQ\Library\Model\TopicMessage;
use Lwz\HyperfRocketMQ\Library\Requests\PublishMessageRequest;
use Lwz\HyperfRocketMQ\Library\Responses\PublishMessageResponse;

class MQProducer
{
    protected $instanceId;
    protected $topicName;
    protected $client;

    public function __construct(HttpClient $client, $instanceId, $topicName)
    {
        if (empty($topicName)) {
            throw new InvalidArgumentException(400, "TopicName is null");
        }
        $this->instanceId = $instanceId;
        $this->client = $client;
        $this->topicName = $topicName;
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }

    public function getTopicName()
    {
        return $this->topicName;
    }

    public function publishMessage(TopicMessage $topicMessage)
    {
        $request = new PublishMessageRequest(
            $this->instanceId,
            $this->topicName,
            $topicMessage->getMessageBody(),
            $topicMessage->getProperties(),
            $topicMessage->getMessageTag()
        );
        $response = new PublishMessageResponse();
        return $this->client->sendRequest($request, $response);
    }
}
