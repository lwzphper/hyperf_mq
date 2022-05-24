<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/20 15:10,
 * @LastEditTime: 2022/05/20 15:10
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ;

use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;

class ConsumerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Consumer($container, $container->get(LoggerFactory::class));
    }
}