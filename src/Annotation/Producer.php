<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/11 22:41,
 * @LastEditTime: 2022/5/11 22:41
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
class Producer extends AbstractAnnotation
{
    /**
     * 驱动
     * @var string
     */
    public string $poolName = 'default';

    /**
     * @var string
     */
    public string $dbConnection = 'default';

    /**
     * @var string
     */
    public string $topic = '';

    /**
     * @var string
     */
    public string $messageKey = '';

    /**
     * @var string
     */
    public string $messageTag = '';
}