<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片插件-处理名片客户日志公共类
 * Author: zal
 * Date: 2020-07-15
 * Time: 10:10
 */

namespace app\plugins\business_card\forms\common;

use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\business_card\models\BusinessCardCustomer;
use app\plugins\business_card\models\BusinessCardCustomerLog;
use yii\base\Exception;

class BusinessCardCustomerLogCommon extends BaseModel
{

    /**
     * 更新客户相关信息
     * @params $params
     * @return array
     */
    public function updateCustomerInfo($params){
        $t = \Yii::$app->db->beginTransaction();
        try{
            $userId = $params["user_id"];
            unset($params["user_id"]);
            $businessCradCustomer = BusinessCardCustomerCommon::getCustomerInfo($userId);
            if(empty($businessCradCustomer)){
                throw new \Exception("数据不存在!");
            }
            $params["id"] = $businessCradCustomer["id"];
            $result = BusinessCardCustomer::operateData($params);
            if ($result !== false) {
                $status = isset($params["status"]) ? $params["status"] : 0;
                if(isset($params["user_type"])){
                    if($businessCradCustomer["user_type"] == $params["user_type"]){
                        throw new \Exception("该类型已经添加过");
                    }else if($businessCradCustomer["user_type"] > $params["user_type"]){
                        throw new \Exception("操作有误");
                    }
                    $userType = $params["user_type"];
                    $log_type = 1;
                    $remark = isset(BusinessCardCustomerLog::$remarks[$userType]) ? BusinessCardCustomerLog::$remarks[$userType] : "添加商机";
                }else{
                    $log_type = isset($params["log_type"]) ? $params["log_type"] : BusinessCardCustomerLog::$statusToLogType[$status];
                    $remark = isset(BusinessCardCustomerLog::$logTypes[$log_type]) ? BusinessCardCustomerLog::$logTypes[$log_type] : "添加商机";
                }
                //状态为新增线索的，是本人主动点击客户按钮授权改变状态的，所以操作人id不是当前用户id
                if($status == BusinessCardCustomer::STATUS_NEW_CLUE){
                    $operateId = $businessCradCustomer["operate_id"];
                    $userId = \Yii::$app->user->id;
                }else{
                    $operateId = \Yii::$app->user->id;
                }
                $data = [];
                $data["operate_id"] = $operateId;
                $data["user_id"] = $userId;
                $data["remark"] = $remark;
                $data["mall_id"] = \Yii::$app->mall->id;
                $data["log_type"] = $log_type;
                $result = BusinessCardCustomerLog::operateData($data);
                if ($result === false) {
                    throw new \Exception("操作失败!");
                }
            } else {
                throw new \Exception("操作失败");
            }
            $t->commit();
            return true;
        }catch (\Exception $ex){
            $t->rollBack();
            \Yii::error("BusinessCardCustomerLogCommon updateCustomerInfo ".CommonLogic::getExceptionMessage($ex));
            throw new \Exception($ex->getMessage());
        }
    }
}

