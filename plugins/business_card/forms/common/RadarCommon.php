<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片插件-boss雷达公共类
 * Author: zal
 * Date: 2020-07-15
 * Time: 10:10
 */

namespace app\plugins\business_card\forms\common;

use app\core\ApiCode;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\plugins\business_card\models\BusinessCardAuth;
use app\plugins\business_card\models\BusinessCardCustomer;
use app\plugins\business_card\models\BusinessCardDepartment;
use app\plugins\business_card\models\BusinessCardRole;

class RadarCommon extends BaseModel
{

    /**
     * 获取用户身份下的部门列表
     * @param $department_id 用户筛选出的部门id
     * @return BusinessCardCustomer|array
     */
    public static function getUserDepartmentList($department_id = 0){
        $departmentList = $returnData = $departmentIds = [];
        $authData = BusinessCardAuthCommon::getBusinessCardAuthIdentity(\Yii::$app->user->id);
        if(empty($authData)){
            return [];
        }
        $departmentId = $authData["department_id"];
        //角色id为1，表示是boss身份
        $departmentParams = ["mall_id" => \Yii::$app->mall->id];
        //boss身份获取所有部门，否则获取指定部门
        if($authData["role_id"] == 1){
            $departmentList = BusinessCardDepartment::getData($departmentParams);
            if(!empty($department_id)){
                $departmentIds[] = $department_id;
            }else{
                foreach ($departmentList as $value){
                    $departmentIds[] = $value["id"];
                }
            }
            $defaultData = [];
            $defaultData[0]["id"] = 0;
            $defaultData[0]["name"] = "全部部门";
            $departmentList = array_merge($defaultData,$departmentList);
        }else{
            //员工没有权限
            if($authData["role_id"] > 2){
                return false;
            }
            //是否有指定部门ID，如果没有则获取用户所属部门id
            $departmentParams["pid"] = !empty($department_id) ? $department_id : $departmentId;
            $list = BusinessCardDepartment::getData($departmentParams);
            if(!empty($list)){
                $departmentList = $list;
                foreach ($departmentList as $value){
                    $departmentIds[] = $value["id"];
                }
                $departmentIds = array_merge($departmentIds,[$departmentId]);
            }else{
                $departmentParams["pid"] = 0;
                $departmentParams["id"] = !empty($department_id) ? $department_id : $departmentId;
                $list = BusinessCardDepartment::getData($departmentParams);
                $departmentList[] = $list;
                $departmentIds = $departmentId;
            }
        }
        //$returnData["auth_data"] = $authData;
        //所属部门下的部门列表
        $returnData["department_list"] = $departmentList;
        //部门下的所有用户
        $returnData["user_id_list"] = self::getDepartmentUsers($departmentIds,$authData["role_id"]);
        //部门下的所有
        $returnData["customer_id_list"] = self::getDepartmentAllCustomer($returnData["user_id_list"]);
        return $returnData;
    }

    /**
     * 获取部门下的用户列表
     * @param $departmentIds 部门id
     * @param $roleId 当前用户的角色id
     * @return BusinessCardCustomer|array
     */
    public static function getDepartmentUsers($departmentIds,$roleId){
        $authParams = $userIds = [];
        $authParams["department_id"] = $departmentIds;
//        if($roleId == 1){
//            //boss身份，获取主管以及员工
//            $authParams["role_id"] = [BusinessCardRole::ID_SUPERVISOR,BusinessCardRole::ID_EMPLOYEE];
//        }else{
//            //主管身份，则是获取所有员工
//            $authParams["role_id"] = BusinessCardRole::ID_EMPLOYEE;
//        }
        $authParams["mall_id"] = \Yii::$app->mall->id;
        $authList = BusinessCardAuth::getData($authParams);
        if(!empty($authList)){
            foreach ($authList as $value){
                $userIds[] = $value["user_id"];
            }
        }
        return $userIds;
    }


    /**
     * 获取部门下的所有客户
     * @param $userIds 部门下的所有成员
     * @return \app\models\BaseActiveQuery|array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    public static function getDepartmentAllCustomer($userIds){
        $returnData = $params = [];
        if(!empty($userIds)){
            $params["operate_id"] = $userIds;
            $params["mall_id"] = \Yii::$app->mall->id;
            $list = BusinessCardCustomer::getData($params);
            foreach ($list as $value){
                $returnData[] = $value["user_id"];
            }
        }
        return $returnData;
    }

    /**
     * 获取总客户数
     * @param $userIds
     * @return array
     */
    public static function getTotalCustomer($userIds){
        $returnData = [];
        $params = [];
        //直推人数,剔除主管人数
        $directPushTotal = count($userIds) - 1;
        //间推人数
        $params["user_id"] = $userIds;
        $params["flag"] = 2;
        $params["group"] = "up.user_id";
        $spacePushList = UserLogic::getUserTeamPushList($params,"all");
        $spacePushTotal = 0;
        if(!empty($spacePushList["list"])){
            //间推用户
            foreach ($spacePushList["list"] as $item) {
                //间推不在直推用户中，才能算一个间推人数
                if(!in_array($item["user_id"],$userIds)){
                    $spacePushTotal++;
                }
            }
        }
        $returnData["direct_push_total"] = intval($directPushTotal);
        $returnData["space_push_total"] = intval($spacePushTotal);
        return $returnData;
    }

}

