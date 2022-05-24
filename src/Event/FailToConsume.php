<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/20 10:18,
 * @LastEditTime: 2022/05/20 10:18
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Event;


use Lwz\HyperfRocketMQ\Message\ConsumerMessageInterface;
use Throwable;

class FailToConsume extends ConsumeEvent
{
    /**
     * @var Throwable
     */
    protected Throwable $throwable;

    public function __construct(ConsumerMessageInterface $message, Throwable $throwable)
    {
        parent::__construct($message);
        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}