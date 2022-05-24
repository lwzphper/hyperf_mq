<?php
/**
 * @Author: laoweizhen <1149243551@qq.com>,
 * @Date: 2022/5/10 22:54,
 * @LastEditTime: 2022/5/10 22:54
 */

declare(strict_types=1);

return [
    'default' => [ // 分组名，基于 host、port、scheme 进行区分
        'host' => env('ROCKETMQ_HTTP_ENDPOINT'),
        'access_key' => env('ROCKETMQ_ACCESS_KEY'),
        'secret_key' => env('ROCKETMQ_SECRET_KEY'),
        'instance_id' => env('ROCKETMQ_INSTANCE_ID'),
        'pool' => [ // producer、consumer 共用
            'min_connections' => 10,
            'max_connections' => 50,
            'connect_timeout' => 3.0,
            'wait_timeout' => 30.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
    ],
];