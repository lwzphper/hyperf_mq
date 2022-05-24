<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/22 22:44,
 * @LastEditTime: 2022/5/22 22:44
 */
declare(strict_types=1);

namespace Lwz\HyperfRocketMQ\Model;

use Hyperf\DbConnection\Model\Model;

class MqErrorLog extends Model
{
    protected $table = 'mq_error_log';
}