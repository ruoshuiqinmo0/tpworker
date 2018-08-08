<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
       echo json_encode(['errcode'=>0,'msg'=>'ok','token'=>'11111','code'=>123456,'login'=>1,'fee'=>'a123456','phone'=>'13617478003']);
        echo json_encode([
            'errorcode'=>123,
            'messag'=>[
                ['ok'=>1,2,3],
                'message'=>[4,5,6],
                'code'=>[7,8,9]
            ]
        ]);
    	return view();
    }
}
