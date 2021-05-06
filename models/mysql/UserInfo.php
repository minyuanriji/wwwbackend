<?php
namespace app\models\mysql;
class UserInfo extends Common{
    public function getUserOpenid($id){
        return $this -> find() -> where(['user_id' => $id,'platform' => 'wechat']) -> select('openid') -> asArray() -> one();
    }
}