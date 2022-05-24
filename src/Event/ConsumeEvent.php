<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/20 10:17,
 * @LastEditTime: 2022/05/20 10:17
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Event;

use Lwz\HyperfRocketMQ\Message\ConsumerMessageInterface;

class ConsumeEvent
{
    /**
     * @var ConsumerMessageInterface
     */
    protected $message;

    public function __construct(ConsumerMessageInterface $message)
    {
        $this->message = $message;
    }

    public function getMessage(): ConsumerMessageInterface
    {
        return $this->message;
    }
}