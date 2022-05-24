<?php
namespace Lwz\HyperfRocketMQ\Library;

use Lwz\HyperfRocketMQ\Library\Exception\MQException;

class AsyncCallback
{
    protected $succeedCallback;
    protected $failedCallback;

    public function __construct(callable $succeedCallback, callable $failedCallback)
    {
        $this->succeedCallback = $succeedCallback;
        $this->failedCallback = $failedCallback;
    }

    public function onSucceed($result)
    {
        return call_user_func($this->succeedCallback, $result);
    }

    public function onFailed(MQException $e)
    {
        return call_user_func($this->failedCallback, $e);
    }
}
