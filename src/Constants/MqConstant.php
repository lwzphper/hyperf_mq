<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/22 23:02,
 * @LastEditTime: 2022/5/22 23:02
 */
declare(strict_types=1);
namespace Lwz\HyperfRocketMQ\Constants;
class MqConstant
{
    // mq生产状态
    public const PRODUCE_STATUS_WAIT = 1; // 待发送
    public const PRODUCE_STATUS_SENDING = 2; // 发送中
    public const PRODUCE_STATUS_SENT = 3; // 已发送
}