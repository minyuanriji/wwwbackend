<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户行为轨迹接口处理类
 * Author: zal
 * Date: 2020-07-06
 * Time: 14:30
 */

namespace app\plugins\business_card\forms\api;

use app\core\ApiCode;
use app\forms\common\WechatCommon;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\business_card\forms\common\Common;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardCustomer;
use app\plugins\business_card\models\BusinessCardCustomerLog;

class BusinessCardCustomerLogForm extends BaseModel
{
    public $page = 1;
    public $limit = 10;

    public $iv;
    public $encryptedData;

    public function rules()
    {
        return [
            [['limit'], 'default', 'value' => 10],
            [['page'], 'integer'],
            [['iv','encryptedData'], 'string'],
        ];
    }

    /**
     * 获取列表
     * @return array
     */
    public function getList()
    {
        $returnData["list"] = $params = [];
        $params["operate_id"] = \Yii::$app->user->identity->id;
        $params["page"] = $this->page;
        $params["limit"] = $this->limit;
        $params["operator"] = 1;
        $fields = ['id','operate_id','user_id','remark','created_at'];
        $list = BusinessCardCustomerLog::getData($params,$fields);
        if(isset($list["list"]) && !empty($list["list"])){
            foreach ($list["list"] as $value){
                $value["date"] = date("Y-m-d",$value["created_at"]);
                $value["time"] = date("H:i",$value["created_at"]);
                $value["text"] = "新增记录：".$value["remark"];
                $value["user_data"]["nickname"] = !empty($value["operator"]["nickname"]) ? $value["operator"]["nickname"] : $value["operator"]["mobile"];
                $value["user_data"]["avatar_url"] = $value["operator"]["avatar_url"];
                unset($value["created_at"],$value["operator"]);
                $returnData["list"][] = $value;
            }
        }
        $returnData["pagination"] = isset($list["pagination"]) ? $this->getPaginationInfo($list["pagination"]) : [];
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $returnData);
    }

    /**
     * 新增线索
     * @return array
     */
    public function addNewClue(){
        try{
            $wechatCommon = new WechatCommon();
            $wechatCommon->iv = $this->iv;
            $wechatCommon->encryptedData = $this->encryptedData;
            $result = $wechatCommon->getAuthorizedMobilePhone();
            if(!empty($result)){
                $businessCardCustomerForm = new BusinessCardCustomerForm();
                $businessCardCustomerForm->user_id = \Yii::$app->user->id;
                $businessCardCustomerForm->status = BusinessCardCustomer::STATUS_NEW_CLUE;
                $result = $businessCardCustomerForm->updateStatus();
                if($result["code"] == ApiCode::CODE_SUCCESS){
                    //更新名片授权信息
                    $returnData = Common::getBusinecardInfo();
                    $updateData = [];
                    $updateData["id"] = $returnData["id"];
                    $updateData["is_auth"] = 1;
                    $updateData["auth_mobile"] = $result["mobile"];
                    BusinessCard::operateData($updateData);
                }
                return $result;
            }
        }catch (\Exception $ex){
            \Yii::error("businessCardCustomer addNewClue error=".CommonLogic::getExceptionMessage($ex));
        }
        return $this->returnApiResultData(ApiCode::CODE_FAIL, "授权失败 ");
    }
}