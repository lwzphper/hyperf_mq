<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/22 23:41,
 * @LastEditTime: 2022/5/22 23:41
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Event;


use Lwz\HyperfRocketMQ\Message\ProducerMessageInterface;

class ProduceEvent
{
    /**
     * @var ProducerMessageInterface
     */
    protected ProducerMessageInterface $message;

    public function __construct(ProducerMessageInterface $message)
    {
        $this->message = $message;
    }

    public function getMessage(): ProducerMessageInterface
    {
        return $this->message;
    }
}