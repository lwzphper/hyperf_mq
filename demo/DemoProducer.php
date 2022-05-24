<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/05/24 11:16,
 * @LastEditTime: 2022/05/24 11:16
 */
declare(strict_types=1);

use Lwz\HyperfRocketMQ\Annotation\Producer;
use Lwz\HyperfRocketMQ\Message\ProducerMessage;

#[Producer(topic:"Topic_03_test", messageTag:"hyperf_test")]
class DemoProducer extends ProducerMessage
{
    public function __construct(array $data)
    {
        // 设置消息内容
        $this->setPayload($data);
        // 自定义messageKey（不定义，会自动生成）
        // $this->setMessageKey('message_key');
    }
}