# hyperf_rocketmq
基于 https://github.com/thefair-net/hyperf_rocketmq 进行封装
### 1、安装

```shell
composer require lwz/hyperf-rocketmq
```

### 2、配置

#### 发布配置

```shell
php bin/hyperf.php vendor:publish lwz/hyperf-rocketmq
```

#### 配置说明

| 配置        | 类型   | 默认值 | 备注                 |
| ----------- | ------ | ------ | -------------------- |
| host        | string |        | HTTP协议客户端接入点 |
| access_key  | string |        | AccessKey ID         |
| secret_key  | string |        | AccessKey Secret     |
| instance_id | string |        | 实例id               |
| pool        | array  |        | 连接池配置           |

```php
return [
    'default' => [ // 分组名，基于 host、port、scheme 进行区分
        'host' => env('ROCKETMQ_HTTP_HOST'),
        'access_key' => env('ROCKETMQ_HTTP_ACCESS_KEY_ID'),
        'secret_key' => env('ROCKET_MQ_HTTP_ACCESS_KEY_SECRET'),
        'instance_id' => env('ROCKET_MQ_HTTP_INSTANCE_ID'),
        'pool' => [
            'min_connections' => 50,
            'max_connections' => 300,
            'connect_timeout' => 3.0,
            'wait_timeout' => 30.0,
            'heartbeat' => -1,
            'max_idle_time' => 60.0,
        ],
    ],
];
```

### 3、投递消息

Producer注解参数

| 字段名       | 类型   | 描述                                          | 默认值   |
| ------------ | ------ | --------------------------------------------- | -------- |
| poolName     | string | 连接池名称。对应配置文件 rocketmq.php 中的key | default  |
| dbConnection | string | 数据库连接名称（可靠投递时使用）              | default  |
| topic        | string | topic                                         | 无       |
| messageKey   | string | 消息key                                       | 随机生成 |
| messageTag   | string | 消息标签                                      | 无       |



#### 3.1 定义生产者相关信息

在 DemoProducer 文件中，我们可以修改 `@Producer` 注解对应的字段来替换对应的 `poolName`、`topic`、`messageTag`。就是最终投递到消息队列中的数据，所以我们可以随意改写 `__construct` 方法，只要最后赋值 `payload` 即可。

> 使用 `@Producer` 注解时需 `use Lwz\HyperfRocketMQ\Annotation\Producer;` 命名空间；

```shell
<?php
declare(strict_types=1);

namespace App\Test\Queue\Producer;

use Lwz\HyperfRocketMQ\Annotation\Producer;
use Lwz\HyperfRocketMQ\Message\ProducerMessage;

#[Producer(topic:"Topic_03_test", messageTag:"tMsgKey")]
class DemoProducer extends ProducerMessage
{
    public function __construct(array $data)
    {
        // 设置消息内容
        $this->setPayload($data);
        // 自定义messageKey（不定义，会自动生成）
        $this->setMessageKey('xxxxx');
    }
}
```

#### 3.2 普通投递方式

通过`Lwz\HyperfRocketMQ\Producer`实例，即可投递消息。

```php
<?php

declare(strict_types=1);

namespace App\Test\Controller;

use App\Test\Queue\Producer\DemoProducer;
use Core\Abstracts\AbstractController;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Lwz\HyperfRocketMQ\Producer;

#[Controller(prefix: "test/bar")]
class BarController extends AbstractController
{
    /**
     * @Inject
     * @var Producer
     */
    protected Producer $producer;

    #[GetMapping("index")]
    public function index()
    {
        $demoProducer = new DemoProducer(['test' => 12345, 'name' => '张三']);
        $this->producer->produce($demoProducer);
        return $this->response->success();
    }
}
```

#### 3.3 消息可靠投递方式

> 目前，消息投递时Rocketmq返回成功响应，就视为投递成功（暂不考虑Rocketmq缓存丢失的问题）。

1. 执行以下命令，生成相关的数据表

   ```shell
   php bin/hyperf.php migrate --path=migrations/rocketmq
   ```

2. 使用示例

   ```php
   $demoProducer = new BarProducer(['test' => 12345, 'name' => '张三1231']);
   
   Db::beginTransaction();
   try{
       // todo 业务逻辑
   
       // 记录消息状态
       $demoProducer->saveMessageStatus();
       Db::commit();
   } catch(\Throwable $ex){
       Db::rollBack();
   }
   
   // 推送消息
   $this->producer->produce($demoProducer);
   ```

### 4、消息消费

Consumer注解属性说明

| 属性         | 类型   | 描述                                          | 默认值  |
| ------------ | ------ | --------------------------------------------- | ------- |
| name         | string | 消费名称                                      | 无      |
| poolName     | string | 连接池名称。对应配置文件 rocketmq.php 中的key | default |
| topic        | string | topic                                         | 无      |
| groupId      | string | 消费组id                                      | 无      |
| messageTag   | string | 消息标签                                      | 无      |
| numOfMessage | int    | 每次拉取消息数                                | 3       |
| waitSeconds  | int    | 轮询等待时间                                  | 3       |
| processNums  | int    | 启动消费进程数                                | 1       |
| enable       | bool   | 是否初始化启动进程                            | true    |

在 DemoConsumer文件中，我们可以修改 `@Consumer` 注解对应的字段来替换对应的 `topic`、`groupId`、`messageTag`。

> 使用 `@Consumer` 注解时需 `use Lwz\HyperfRocketMQ\Annotation\Consumer;` 命名空间；

```php
use Lwz\HyperfRocketMQ\Annotation\Consumer;
use Lwz\HyperfRocketMQ\Library\Model\Message as RocketMQMessage;
use Lwz\HyperfRocketMQ\Message\ConsumerMessage;

#[Consumer(topic:"Topic_03_test",groupId: "test_test", messageTag:"tMsgKey")]
class DemoConsumer extends ConsumerMessage
{
    public function consumeMessage(RocketMQMessage $rocketMQMessage)
    {
        $msgTag = $rocketMQMessage->getMessageTag(); // 消息标签
        $msgKey = $rocketMQMessage->getMessageKey(); // 消息唯一标识
        $msgBody = $this->unserialize($rocketMQMessage->getMessageBody()); // 消息体
        $msgId = $rocketMQMessage->getMessageId();

        dump('消费端接收到消息：',$msgBody);
    }
}
```

