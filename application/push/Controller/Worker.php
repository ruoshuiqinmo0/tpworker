<?php

namespace app\push\controller;

use think\worker\Server;
use Workerman\Lib\Timer;

class Worker extends Server
{
    protected $socket = 'websocket://192.168.1.179:2346';
    protected $processes = 4;

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        // 客户端传递的是json数据
        $message_data = json_decode($data, true);
        if (!$message_data) {
            return;
        }
        // 根据类型执行不同的业务
        switch ($message_data['type']) {
            // 客户端回应服务端的心跳
            case 'pong':

                return;

            // 客户端登录 message格式: {type:login, name:xx,,' room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'login':
                if (!isset($message_data['room_id'])) {
                    throw new \Exception("\$message_data['room_id'] not set. client_ip:{$_SERVER['REMOTE_ADDR']} \$message:$message");
                }
                if (!isset($connection->uid)) {
                    // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
                    $connection->uid = $message_data['friend'];
                    /* 保存uid到connection的映射，这样可以方便的通过uid查找connection，
                     * 实现针对特定uid推送数据
                     */
                    $this->worker->connection[$connection->uid] = $connection;
                    //$this->worker->Connection = $connection;

                    foreach ($this->worker->connections as $conn) {
                        if ($conn->uid != $message_data['friend']) {
                            $conn->send("{'type':'login','data':'" .$connection->uid."''}");
                        }
                    }
                }
                break;
            case 'say':
                $this->sendMsg($message_data);
//                $worker = $this->worker;
//                foreach($worker->connections as $conn)
//                {
//                     $conn->send("{'type':'say','data':'".$message_data['data'].'进程id'.$worker->id."'}");
                //$string = implode(' ', $worker->connections);
                //$conn->send("{'type':'say','data':'当前对象链接对象id'}");
                // $conn->send("{'type':'say','data':'当前对象链接对象id'".json_encode($worker->connections)."'}");
//                }
                break;
        }
    }

    //单独发送指定用户
    protected function sendMsg( $message_data)
    {

        $friend = $message_data['friend'];
       // if (isset($this->worker->uidConnections[$friend])) {
            $message = [
                'type' => 'say',
                'data' => $message_data['data'],
            ];
            //$connection = $this->worker->connections[$friend];
           // $connection->send(json_encode($message));
            foreach ($this->worker->connections as $conn) {
                if ($conn->uid = $message_data['friend']) {
                    $conn->send(json_encode($message));
                }
            }
       // }


    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        echo $connection->id."\n\r";
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
        foreach ($worker->uidConnections as $connection) {
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