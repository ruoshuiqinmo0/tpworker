<?php

namespace app\push\controller;

use think\worker\Server;
use Workerman\Lib\Timer;

class Worker extends Server
{
    protected $socket = 'websocket://192.168.142.1:2346';
    protected $processes = 1;

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $json_data)
    {
        // 客户端传递的是json数据
        $message_data = json_decode($json_data, true);
        if (!$message_data) {
            return;
        }
        // 根据类型执行不同的业务
        switch ($message_data['type']) {
            // 客户端回应服务端的心跳
            case 'pong':

                return;

            // 客户端登录 message格式: {type:login, friend:xx}
            case 'login':
                if (!isset($connection->uid)) {
                    // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）

                    $connection->uid = $message_data['friend'];

                    /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                     * 实现针对特定uid推送数据
                     */
                    $this->worker->uidconnections[$connection->uid] = $connection;

                    foreach ($this->worker->uidconnections as $conn) {
                        if ($conn->uid != $message_data['friend']) {
                            $conn->send("{'type':'login','data':'" .$connection->uid."'}");
                        }
                    }
                }
                break;
            case 'say':
                $this->sendMsgByFriend($message_data);
                break;
        }
    }

    //单独发送指定用户
    protected function sendMsgByFriend($message_data)
    {
        $worker = $this->worker;
        if(isset($worker->uidconnections[$message_data['friend']]))
        {
            $connection = $worker->uidconnections[$message_data['friend']];
            $message = [
                'type' => 'say',
                'data' => $message_data['data'],
            ];
            $connection->send(json_encode($message));
        }
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {

    }

    /**
     * 向所有验证的用户发送消息
     */
    public function sendAllMessage($data)
    {
        $worker = $this->worker;
        $message = [
            'type' => 'say',
            'data' => $data
        ];

        foreach ($worker->uidconnections as $connection) {
            $connection->send($message);
        }
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        $worker = $this->worker;
        if (isset($worker->uidConnections->uid)) {
            // 连接断开时删除映射
            unset($worker->uidConnections[$connection->uid]);
        }
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
//        $worker = $this->worker;
//        Timer::add(1, function()use($worker){
//            foreach($worker->connections as $connection) {
//                $connection->send('{"type":"say","data":"hello'.date('Ymd H:i:s').'"}');
//            }
//        });
    }
}