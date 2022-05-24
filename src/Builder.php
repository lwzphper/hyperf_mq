<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/11 23:10,
 * @LastEditTime: 2022/5/11 23:10
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ;

use Hyperf\Guzzle\PoolHandler;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Coroutine;
use Lwz\HyperfRocketMQ\Library\MQClient;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class Builder
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var null|EventDispatcherInterface
     */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @var LoggerFactory
     */
    protected LoggerFactory $loggerFactory;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container, LoggerFactory $loggerFactory)
    {
        $this->container = $container;
        $this->loggerFactory = $loggerFactory;
        if ($container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        }
    }

    protected function getClient(Config $config): MQClient
    {
        return new MQClient(
            $config->getHost(), $config->getAccessKey(), $config->getSecretKey(), null, $this->getMQConfig($config)
        );
    }

    protected function setLogger(string $groupName): void
    {
        $this->logger = $this->loggerFactory->get('rocketmq_log', $groupName ?: 'default');
    }

    /**
     * 配置文件转换
     * @param Config $config
     * @return Library\Config
     */
    protected function getMQConfig(Config $config): \Lwz\HyperfRocketMQ\Library\Config
    {
        $mqConfig = new \Lwz\HyperfRocketMQ\Library\Config();
        $mqConfig->setConnectTimeout($config->getConnectTimeout());
        $mqConfig->setRequestTimeout($config->getWaitTimeout());
        $mqConfig->setHandler(make(PoolHandler::class, [
            'option' => [
                'min_connections' => $config->getMinConnections(),
                'max_connections' => $config->getMaxConnection(),
                'connect_timeout' => $config->getConnectTimeout(),
                'wait_timeout' => $config->getWaitTimeout(),
                'heartbeat' => $config->getHeartBeat(),
                'max_idle_time' => $config->getMaxIdleTime(),
            ]
        ]));
        return $mqConfig;
    }

    protected function isCoroutine(): bool
    {
        return Coroutine::id() > 0;
    }
}