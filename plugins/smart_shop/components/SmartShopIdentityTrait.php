<?php

namespace app\plugins\smart_shop\components;

trait SmartShopIdentityTrait
{
    /**
     * 通过OPENID查找用户
     * @param $openid
     * @param $ali_uid
     */
    public function findUsersByOpenid($openid, $ali_uid){
        $row = null;
        if(!empty($openid)){
            $sql = "SELECT id,nickname,avatar,mobile FROM {{%users}} WHERE openid='{$openid}' OR user_id='{$ali_uid}'";
            $row = $this->getDB()->createCommand($sql)->queryOne();
        }
        return $row;
    }

    /**
     * 验证token
     * @param $token
     * @return boolean
     */
    public function validateToken($token){
        $row = null;
        if(!empty($token)){
            $sql = "SELECT id FROM {{%admin}} WHERE token='{$token}'";
            $row = $this->getDB()->createCommand($sql)->queryOne();
        }
        return $row && isset($row['id']) ? true : false;
    }
}