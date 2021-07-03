<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片插件-处理名片权限公共类
 * Author: zal
 * Date: 2020-07-30
 * Time: 10:10
 */

namespace app\plugins\business_card\forms\common;

use app\models\BaseModel;
use app\plugins\business_card\models\BusinessCardAuth;
use app\plugins\business_card\models\BusinessCardRole;

class BusinessCardAuthCommon extends BaseModel
{

    /**
     * 获取员工名片身份
     * @param $user_id
     * @param $fields
     * @return BusinessCardAuth|array
     */
    public static function getBusinessCardAuthIdentity($user_id,$fields = []){
        /** @var BusinessCardAuth $businessCradAuth */
        $params = [];
        $params["mall_id"] = \Yii::$app->mall->id;
        $params["user_id"] = $user_id;
        $params["user"] = 1;
        $params["is_one"] = 1;
        $businessCradAuth = BusinessCardAuth::getData($params,$fields);
        return $businessCradAuth;
    }

    /**
     * 添加名片权限数据
     * @param $userId
     * @param $mallId
     * @param $departmentId
     * @param $positionId
     * @param int $role_id
     * @return bool
     */
    public static function addData($userId,$mallId,$departmentId,$positionId,$role_id = BusinessCardRole::ID_EMPLOYEE){
        $existData = self::getBusinessCardAuthIdentity($userId,["id","user_id"]);
        if(!empty($existData)){
            return true;
        }
        $data = [];
        $data["user_id"] = $userId;
        $data["mall_id"] = $mallId;
        $data["department_id"] = $departmentId;
        $data["position_id"] = $positionId;
        $data["role_id"] = $role_id;
        $result = BusinessCardAuth::operateData($data);
        return $result;
    }
}

