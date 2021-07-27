<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片插件-处理名片行为轨迹公共类
 * Author: zal
 * Date: 2020-07-15
 * Time: 10:10
 */

namespace app\plugins\business_card\forms\common;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\business_card\models\BusinessCardCustomer;

class BusinessCardCustomerCommon extends BaseModel
{

    /**
     * 获取客户信息，如果不存在就新增
     * @param $user_id
     * @param $fields
     * @return BusinessCardCustomer|array
     */
    public static function getCustomerInfo($user_id,$fields = []){
        /** @var BusinessCardCustomer $businessCradCustomer */
        $businessCradCustomer = BusinessCardCustomer::getData(["mall_id" => \Yii::$app->mall->id,"user_id" => $user_id,'user' => 1,'is_one' => 1],$fields);

        if(empty($businessCradCustomer) && $user_id != \Yii::$app->user->id){
            $businessCradCustomer["mall_id"] = \Yii::$app->mall->id;
            $businessCradCustomer["user_id"] = $user_id;
            $businessCradCustomer["operate_id"] = \Yii::$app->user->id;
            $businessCradCustomer["is_tag"] = BusinessCardCustomer::IS_TAG_NO;
            $result = BusinessCardCustomer::operateData($businessCradCustomer);
            if($result === false){
                return false;
            }
            $businessCradCustomer["id"] = $result;
        }
        return $businessCradCustomer;
    }
}

