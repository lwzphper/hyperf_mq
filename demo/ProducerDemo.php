<?php
declare(strict_types=1);

use Hyperf\Utils\ApplicationContext;
use Lwz\HyperfRocketMQ\Producer;

$producer = new DemoProducer(['test' => '张三', 'age' => 30]);
ApplicationContext::getContainer()->get(Producer::class)->produce($producer);