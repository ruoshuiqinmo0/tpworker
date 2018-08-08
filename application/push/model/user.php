<?php

namespace app\push\model;

use PHPUnit\Framework\Exception;
use think\Model;

class user extends Model
{
    protected $autoWriteTimestamp = true;

    //添加新用户
    public function createUser($data){
        if($this->isNewUser($data)){
             self::create($data);
        }
        return $this->createToken();
    }

    //判断是否是新用户
    public function isNewUser($data){
        $res = self::where('imsi',$data['imsi'])->whereOr('imei',$data['imei'])->find();
        if(!is_null($res)){
            return false;
        }
        return true;
    }

    public function createToken($uid){
        return md5(time().rand(1,99999));
    }
}
