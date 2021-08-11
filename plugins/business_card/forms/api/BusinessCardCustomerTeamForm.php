<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 客户团队接口处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\business_card\forms\api;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\plugins\business_card\forms\common\BusinessCardCustomerCommon;
use app\plugins\business_card\models\BusinessCardCustomer;

class BusinessCardCustomerTeamForm extends BaseModel
{
    public $user_id;
    public $user_type = 0;
    public $page = 1;
    public $limit = 10;
    public $flag;
    public $keywords;


    public function rules()
    {
        return [
            [['user_id','user_type','page','limit','flag'], 'integer'],
            [["keywords"],"string"]
        ];
    }

    /**
     * 团队列表
     * @param array $clientParams
     * @return array
     */
    public function teamList($clientParams = []){
        $userStatData = $data = $returnData = [];
        $userId = empty($this->user_id) ? \Yii::$app->user->id : $this->user_id;
        //用户的关系链
        if($this->user_type > 0){
            $params = [];
            $params["user_type"] = $this->user_type;
            $params["operate_id"] = $userId;
            $params["page"] = $this->page;
            $params["limit"] = $this->limit;
            $params["user"] = 1;
            $params["keywords"] = $this->keywords;
            //如果存在status，则是调用我的线索数据
            if(isset($clientParams["status"])){
                $params["status"] = $clientParams["status"];
            }
            BusinessCardCustomer::$keywords = $params["keywords"];
            $list = BusinessCardCustomer::getData($params);
            $list["pagination"] = $this->getPaginationInfo($list["pagination"]);
            $userStatData["direct_push_total"] = $list["pagination"]["total_count"];
            $userStatData["space_push_total"] = 0;
        }else{
            $list = UserLogic::getUserTeamPushList($this->attributes);
            $userStatData = UserLogic::getStatUserPushTotal(["user_id" => $userId,["keywords" =>$this->keywords]]);
        }

        //直推
//        if($this->flag == 1){
//            $params = [];
//            $params["user_type"] = $this->user_type;
//            $params["operate_id"] = $userId;
//            $params["page"] = $this->page;
//            $params["limit"] = $this->limit;
//            $params["user"] = 1;
//            $params["keywords"] = $this->keywords;
//            //如果存在status，则是调用我的线索数据
//            if(isset($clientParams["status"])){
//                $params["status"] = $clientParams["status"];
//            }
//            BusinessCardCustomer::$keywords = $params["keywords"];
//            $list = BusinessCardCustomer::getData($params);
//            $list["pagination"] = $this->getPaginationInfo($list["pagination"]);
//        }else{
//            //间推
//            $list = UserLogic::getUserTeamPushList($this->attributes);
//            if(!empty($list["list"])){
//                foreach ($list["list"] as $value){
//                    $customerUserIds[] = $value["user_id"];
//                }
//                $params = [];
//                $params["user_type"] = $this->user_type;
//                $params["user_id"] = $customerUserIds;
//                $params["page"] = $this->page;
//                $params["limit"] = $this->limit;
//                $params["user"] = 1;
//                $params["keywords"] = $this->keywords;
//                //如果存在status，则是调用我的线索数据
//                if(isset($clientParams["status"])){
//                    $params["status"] = $clientParams["status"];
//                }
//                BusinessCardCustomer::$keywords = $params["keywords"];
//                $list = BusinessCardCustomer::getData($params);
//            }
//        }
        if(!empty($list["list"])){
            foreach ($list["list"] as $value){
                $data["user_id"] = $value["user_id"];
                //$basicInfo = json_decode($value["basic_info"],true);
                $mobile = isset($value["mobile"]) ? $value["mobile"] : $value["user"]["mobile"];
                if(isset($value["user"])){
                    $nickName = isset($value["user"]["nickname"]) && !empty($value["user"]["nickname"]) ? $value["user"]["nickname"] : $value["user"]["mobile"];
                }else{
                    $nickName = isset($value["nickname"]) && !empty($value["nickname"]) ? $value["nickname"] : $value["mobile"];
                }
                $data["nickname"] = $nickName;
                $data["avatar_url"] = isset($value["user"]["avatar_url"]) ? $value["user"]["avatar_url"] : "";
                $data["mobile"] = $mobile;
                $params = [];
                $params["user_type"] = $this->user_type;
                $params["operate_id"] = $userId;
                $params["is_one"] = 1;
                //名片客户资料
                if($this->user_type == 0){
                    $customers = BusinessCardCustomerCommon::getCustomerInfo($data["user_id"]);
                    $userType = !empty($customers) ? $customers["user_type"] : 0;
                    $statusName = BusinessCardCustomer::$userTypeData[$userType];
                }else{
                    $customers = $value;
                    $statusName = BusinessCardCustomer::$statusData[$customers["status"]];
                }
                $data["status_name"] = $statusName;
                $returnData["list"][] = $data;
            }
        }else{
            $returnData["list"] = [];
        }
//        $userStatData = [];
//        $userStatData["direct_push_total"] = $this->getCustomerPushTotal(1);
//        $userStatData["space_push_total"] = $this->getCustomerPushTotal(2);
        $returnData["stat"] = $userStatData;
        $returnData["pagination"] = $list["pagination"];
        if(!empty($clientParams)){
            return $returnData;
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"设置成功",$returnData);
    }

    /**
     * 获取直推间推总数
     * @param $flag 1直推2间推
     * @param $type
     * @return \app\models\BaseActiveQuery|array|int|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    private function getCustomerPushTotal($flag,$type = "single"){
        $params = $customerUserIds = [];
        $userId = empty($this->user_id) ? \Yii::$app->user->id : $this->user_id;
        $total = 0;
        if($type == "single"){
            $params["user_type"] = $this->user_type;
        }
        if($flag  == 1){
            $params["operate_id"] = $userId;
            $params["return_count"] = 1;
            $total = BusinessCardCustomer::getData($params);
        }else{
            $searchParams = $this->attributes;
            $searchParams["flag"] = $flag;
            $list = UserLogic::getUserTeamPushList($searchParams);
            if(!empty($list["list"])) {
                foreach ($list["list"] as $value) {
                    $customerUserIds[] = $value["user_id"];
                }
                $params["user_id"] = $customerUserIds;
                $params["return_count"] = 1;
                $total = BusinessCardCustomer::getData($params);
            }
        }
        return intval($total);
    }

    /**
     * 我的客户
     * @return array
     */
    public function myClient(){
        $data = $headerStat = [];
        $params = [];
        $params["mall_id"] = \Yii::$app->mall->id;
        $params["operate_id"] = \Yii::$app->user->id;
        $params["return_count"] = 1;
        $params["status"] = BusinessCardCustomer::STATUS_FOLLOWING;
        //跟进中人数
        $followTotal = BusinessCardCustomer::getData($params);
        $params["status"] = BusinessCardCustomer::STATUS_DEAL;
        //成交人数
        $dealTotal = BusinessCardCustomer::getData($params);
        $data["keywords"] = $this->keywords;
        $returnData = $this->teamList($data);

        //总直推人数
//        $allDirectPushTotal = $this->getCustomerPushTotal(1,"all");
//        //总间推人数
//        $allSpacePushTotal = $this->getCustomerPushTotal(1,"all");

        $userStatData = UserLogic::getStatUserPushTotal(["user_id" => \Yii::$app->user->id,"keywords" => $this->keywords]);
        //总直推人数
        $allDirectPushTotal = $userStatData["direct_push_total"];
        //总间推人数
        $allSpacePushTotal = $userStatData["space_push_total"];

        $returnData["header_stat"]["client_total"] = intval($allDirectPushTotal) + intval($allSpacePushTotal);
        $returnData["header_stat"]["follow_total"] = intval($followTotal);
        $returnData["header_stat"]["deal_total"] = intval($dealTotal);
        $userTeamList = UserLogic::getUserTeamAllData($this->user_id);
        $teamUserIds = $userTeamList["child_list"];
        $orderStatData = UserLogic::getUserTeamOrderStatInfo($teamUserIds);
        $returnData["header_stat"]["fans_total"] = intval($allDirectPushTotal);
        $returnData["header_stat"]["team_total"] = $returnData["header_stat"]["client_total"];
        $returnData["header_stat"] = array_merge($returnData["header_stat"],$orderStatData);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"",$returnData);
    }

    /**
     * 我的线索
     * @return array
     */
    public function myClue(){
        $data = [];
        $data["status"] = 1;
        $returnData = $this->teamList($data);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"",$returnData);
    }

}