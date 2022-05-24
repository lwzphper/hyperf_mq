<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/11 22:40,
 * @LastEditTime: 2022/5/11 22:40
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Consumer extends AbstractAnnotation
{
    /**
     * @var string
     */
    public string $name = 'Consumer';

    /**
     * 驱动
     * @var string
     */
    public string $poolName = 'default';

    /**
     * @var string
     */
    public string $topic = '';

    /**
     * @var string
     */
    public string $groupId;

    /**
     * filter tag for consumer. If not empty, only consume the message which's messageTag is equal to it.
     * @var string
     */
    public string $messageTag;

    /**
     * consume how many messages once, 1~16
     * @var int
     */
    public int $numOfMessage = 3;

    /**
     * if > 0, means the time(second) the request holden at server if there is no message to consume.
     * If <= 0, means the server will response back if there is no message to consume.
     * It's value should be 1~30
     * @var int
     */
    public int $waitSeconds = 3;

    /**
     * 进程数量
     * @var int
     */
    public int $processNums = 1;

    /**
     * 是否初始化时启动
     * @var bool
     */
    public bool $enable = true;

    /**
     * 进程最大消费数
     * @var int
     */
    public int $maxConsumption = 0;

    /**
     * 是否开启协程并发消费
     * @var bool
     */
    public bool $openCoroutine = false;
}