<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 16:21
 */

namespace app\lib\exception;


use think\exception\Handle;

class ExceptionHandler extends Handle
{
    protected $code;
    protected $msg;
    protected $errorCode;

    public function render(Exception $e)
    {
        //dump($e);
        if ($e instanceof BaseException) {
            //自定义异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            if(config('app_debug')){
                return parent::render($e);
            }
            $this->code = 500;
            $this->msg = ' 服务器内部错误';
            $this->errorCode = 999;
            $this->recordErrorLog($e);
        }
        $request = Request::instance()->url(true);
        $result = [
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => $request,
        ];
        return json($result, $this->code);
    }
}