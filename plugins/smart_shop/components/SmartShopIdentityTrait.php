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
     * @param $admin
     * @param $merchant
     * @param $store
     * @return boolean
     */
    public function validateToken($token, &$admin, &$merchant, &$store){
        $admin = null;
        if(!empty($token)){
            $tokenPart = explode("@", $token);
            if(!empty($tokenPart[0]) && is_numeric($tokenPart[0]) && (time() - $tokenPart[0]) < 1800){
                $sql = "SELECT id,pid,store_id FROM {{%admin}} WHERE token='{$token}'";
                $admin = $this->getDB()->createCommand($sql)->queryOne();
            }
        }
        if($admin){
            if($admin['store_id']){ //门店账号
                $sql = "SELECT * FROM {{%store}} WHERE id='".$admin['store_id']."'";
                $store = $this->getDB()->createCommand($sql)->queryOne();
                $sql = "SELECT * FROM {{%merchant}} WHERE admin_id='".$admin['pid']."'";
                $merchant = $this->getDB()->createCommand($sql)->queryOne();
            }else{ //商户账号
                $sql = "SELECT * FROM {{%store}} WHERE admin_id='".$admin['id']."'";
                $store = $this->getDB()->createCommand($sql)->queryOne();
                $sql = "SELECT * FROM {{%merchant}} WHERE admin_id='".$admin['id']."'";
                $merchant = $this->getDB()->createCommand($sql)->queryOne();
            }
        }
        return $admin && isset($admin['id']) ? true : false;
    }
}