<?php
namespace Lwz\HyperfRocketMQ\Library\Responses;

use Exception;
use Lwz\HyperfRocketMQ\Library\Common\XMLParser;
use Lwz\HyperfRocketMQ\Library\Constants;
use Lwz\HyperfRocketMQ\Library\Exception\InvalidArgumentException;
use Lwz\HyperfRocketMQ\Library\Exception\MalformedXMLException;
use Lwz\HyperfRocketMQ\Library\Exception\MQException;
use Lwz\HyperfRocketMQ\Library\Exception\TopicNotExistException;
use Lwz\HyperfRocketMQ\Library\Model\Message;
use Lwz\HyperfRocketMQ\Library\Model\TopicMessage;
use Throwable;
use XMLReader;

class PublishMessageResponse extends BaseResponse
{
    public function __construct()
    {
    }

    public function parseResponse($statusCode, $content): TopicMessage
    {
        $this->statusCode = $statusCode;
        if ($statusCode == 201) {
            $this->succeed = true;
        } else {
            $this->parseErrorResponse($statusCode, $content);
        }

        $xmlReader = $this->loadXmlContent($content);
        try {
            return $this->readMessageIdAndMD5XML($xmlReader);
        } catch (Exception $e) {
            throw new MQException($statusCode, $e->getMessage(), $e);
        } catch (Throwable $t) {
            throw new MQException($statusCode, $t->getMessage());
        }
    }

    public function readMessageIdAndMD5XML(XMLReader $xmlReader): TopicMessage
    {
        $message = Message::fromXML($xmlReader);
        $topicMessage = new TopicMessage(null);
        $topicMessage->setMessageId($message->getMessageId());
        $topicMessage->setMessageBodyMD5($message->getMessageBodyMD5());
        $topicMessage->setReceiptHandle($message->getReceiptHandle());

        return $topicMessage;
    }

    public function parseErrorResponse($statusCode, $content, MQException $exception = null)
    {
        $this->succeed = false;
        $xmlReader = $this->loadXmlContent($content);
        try {
            $result = XMLParser::parseNormalError($xmlReader);
            if ($result['Code'] == Constants::TOPIC_NOT_EXIST) {
                throw new TopicNotExistException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
            }
            if ($result['Code'] == Constants::INVALID_ARGUMENT) {
                throw new InvalidArgumentException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
            }
            if ($result['Code'] == Constants::MALFORMED_XML) {
                throw new MalformedXMLException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
            }
            throw new MQException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
        } catch (Exception $e) {
            if ($exception != null) {
                throw $exception;
            } elseif ($e instanceof MQException) {
                throw $e;
            } else {
                throw new MQException($statusCode, $e->getMessage());
            }
        } catch (Throwable $t) {
            throw new MQException($statusCode, $t->getMessage());
        }
    }
}
