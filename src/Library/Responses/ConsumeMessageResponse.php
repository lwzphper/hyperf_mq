<?php
namespace Lwz\HyperfRocketMQ\Library\Responses;

use Exception;
use Lwz\HyperfRocketMQ\Library\Common\XMLParser;
use Lwz\HyperfRocketMQ\Library\Constants;
use Lwz\HyperfRocketMQ\Library\Exception\MessageNotExistException;
use Lwz\HyperfRocketMQ\Library\Exception\MQException;
use Lwz\HyperfRocketMQ\Library\Exception\TopicNotExistException;
use Lwz\HyperfRocketMQ\Library\Model\Message;
use Throwable;
use XMLReader;

class ConsumeMessageResponse extends BaseResponse
{
    protected $messages;

    public function __construct()
    {
        $this->messages = array();
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function parseResponse($statusCode, $content): array
    {
        $this->statusCode = $statusCode;
        if ($statusCode == 200) {
            $this->succeed = true;
        } else {
            $this->parseErrorResponse($statusCode, $content);
        }

        $xmlReader = $this->loadXmlContent($content);

        try {
            while ($xmlReader->read()) {
                if ($xmlReader->nodeType == XMLReader::ELEMENT
                    && $xmlReader->name == 'Message') {
                    $this->messages[] = Message::fromXML($xmlReader);
                }
            }
            return $this->messages;
        } catch (Exception $e) {
            throw new MQException($statusCode, $e->getMessage(), $e);
        } catch (Throwable $t) {
            throw new MQException($statusCode, $t->getMessage(), $t);
        }
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
            if ($result['Code'] == Constants::MESSAGE_NOT_EXIST) {
                throw new MessageNotExistException($statusCode, $result['Message'], $exception, $result['Code'], $result['RequestId'], $result['HostId']);
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
