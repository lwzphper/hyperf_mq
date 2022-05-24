<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/10 23:01,
 * @LastEditTime: 2022/5/10 23:01
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ;

use Hyperf\Utils\Packer\JsonPacker;
use Lwz\HyperfRocketMQ\Listener\BeforeMainServerStartListener;
use Lwz\HyperfRocketMQ\Packer\Packer;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Producer::class => Producer::class,
                Packer::class => JsonPacker::class,
                Consumer::class => ConsumerFactory::class,
            ],
            'listeners' => [
                BeforeMainServerStartListener::class => 99,
            ],
            'commands' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for rocketmq.',
                    'source' => __DIR__ . '/../publish/rocketmq.php',
                    'destination' => BASE_PATH . '/config/autoload/rocketmq.php',
                ],
                [
                    'id' => 'mq_status_log_migration',
                    'description' => 'The mq_status_log migration for rocketmq.',
                    'source' => __DIR__ . '/../publish/migrations/2022_05_22_140245_create_mq_status_log_table.php',
                    'destination' => BASE_PATH . '/migrations/rocketmq/2022_05_22_140245_create_mq_status_log_table.php',
                ],
                [
                    'id' => 'mq_error_log_migrations',
                    'description' => 'The mq_error_log migration for rocketmq.',
                    'source' => __DIR__ . '/../publish/migrations/2022_05_22_141058_create_mq_error_log_table.php',
                    'destination' => BASE_PATH . '/migrations/rocketmq/2022_05_22_141058_create_mq_error_log_table.php',
                ],
            ],
        ];
    }
}