<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金接口处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\business_card\forms\api;

use app\core\ApiCode;
use app\forms\api\tag\TagForm;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Tag;
use app\models\User;
use app\plugins\business_card\forms\common\BusinessCardCustomerCommon;
use app\plugins\business_card\forms\common\BusinessCardCustomerLogCommon;
use app\plugins\business_card\forms\common\BusinessCardTrackLogCommon;
use app\plugins\business_card\forms\common\Common;
use app\plugins\business_card\models\BusinessCardCustomer;
use app\plugins\business_card\models\BusinessCardCustomerLog;
use app\plugins\business_card\models\BusinessCardSetting;
use app\plugins\business_card\models\BusinessCardTag;
use app\plugins\business_card\models\BusinessCardTrackLog;
use app\plugins\business_card\models\BusinessCardTrackStat;

class BusinessCardCustomerForm extends BaseModel
{
    public $user_id;
    public $user_type;
    public $remark;
    public $status;
    public $log_type = 1;

    public $id;
    public $basicInfo;
    public $time_type = 1;

    public function rules()
    {
        return [
            [['user_id','user_type','time_type','status','log_type','id'], 'integer'],
            [['remark'],"string"]
        ];
    }

    /**
     * 添加客户类型(新增商机)
     * @return array
     */
    public function add(){
        $data = [];
        if(empty($this->user_type) || empty($this->user_id)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
        }

        $user = User::findOne($this->user_id);
        if(empty($user)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"客户不存在");
        }
        //只有上级才能修改状态
        $parentId = $user->parent_id;
        if($parentId != \Yii::$app->user->id){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"只有直推才能添加商机");
        }
        try{
            $businessCardCustomerLogCommon = new BusinessCardCustomerLogCommon();
            $data["user_type"] = $this->user_type;
            $data["remark"] = $this->remark;
            $data["mall_id"] = \Yii::$app->mall->id;
            $data["log_type"] = $this->log_type;
            $data["user_id"] = $this->user_id;
            $data["status"] = isset(BusinessCardCustomer::$userTypeToStatus[$this->user_type]) ? BusinessCardCustomer::$userTypeToStatus[$this->user_type] : 0;
            $businessCardCustomerLogCommon->updateCustomerInfo($data);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功");
        }catch (\Exception $ex){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$ex->getMessage());
        }

    }

    /**
     * 添加客户状态变更记录
     * @return bool
     */
    private function addCustomerLog(){
        $data = [];
        $data["operate_id"] = \Yii::$app->user->id;
        $data["user_id"] = $this->user_id;
        $data["remark"] = $this->remark;
        $data["mall_id"] = \Yii::$app->mall->id;
        $data["log_type"] = $this->log_type;
        return BusinessCardCustomerLog::operateData($data);
    }

    /**
     * 新增跟进记录（包括私聊，拨打电话）
     * @return array
     */
    public function follow(){
        $result = $this->addCustomerLog();
        if ($result === false) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"操作失败");
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功");
    }

    /**
     * 详情
     * @return array
     */
    public function detail(){
        $businessCradCustomer = BusinessCardCustomerCommon::getCustomerInfo($this->user_id,["id","user_id","basic_info","is_tag"]);
        if(empty($businessCradCustomer)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"数据不存在");
        }
        try{
            $tags = [];
            $tagForm = new TagForm();
            $tagForm->object_id = $this->user_id;
            $tagList = $tagForm->getUserTag();
            if(!empty($tagList)){
                foreach ($tagList as $value){
                    $tags[] = $value["name"];
                }
            }else{
                $businessCardSetting = BusinessCardSetting::getData(\Yii::$app->mall->id);
                $tagList = isset($businessCardSetting[BusinessCardSetting::TAG]) ? $businessCardSetting[BusinessCardSetting::TAG] : [];
                $length = count($tagList);
                $length = $length > 7 ? 8 : $length;
                $keys = array_rand($tagList,$length);
                $tagData = [];
                foreach ($tagList as $key => $value){
                    if(in_array($key,$keys)){
                        $tagData[] = $value;
                    }
                }
                $tags = $tagData;
            }

            $userData = isset($businessCradCustomer["user"]) ? $businessCradCustomer["user"] : [];
            if(isset($businessCradCustomer["user"])){
                unset($businessCradCustomer['user']);
            }
            if(!empty($businessCradCustomer["basic_info"])){
                $businessCradCustomer["basic_info"] = json_decode($businessCradCustomer["basic_info"],true);
            }
            $diyTags = BusinessCardTag::getData(["user_id" => $this->user_id,"is_diy" => BusinessCardTag::IS_DIY_YES,"limit" => 10],["name"]);
            $businessCradCustomer["user"]["avatar_url"] = !empty($userData) ? $userData["avatar_url"] : "";
            $businessCradCustomer["user"]["nickname"] = !empty($userData) ? $userData["nickname"] : "";
            $businessCradCustomer["auto_tag"] = $tags;
            $businessCradCustomer["diy_tag"] = $diyTags;
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"",$businessCradCustomer);
        }catch (\Exception $ex){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"未配置标签数据".$ex->getMessage());
        }
    }

    /**
     * 客户基本信息设置
     * @return array
     */
    public function setting(){
        $t = \Yii::$app->db->beginTransaction();
        try{
            if(empty($this->id)){
                $t->rollBack();
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
            }
            $params = [];
            $params["from"] = isset($this->basicInfo["from"]) ? $this->basicInfo["from"] : "";
            $params["full_name"] = isset($this->basicInfo["full_name"]) ? $this->basicInfo["full_name"] : "";
            $params["sex"] = isset($this->basicInfo["sex"]) ? $this->basicInfo["sex"] : "男";
            $params["mobile"] = isset($this->basicInfo["mobile"]) ? $this->basicInfo["mobile"] : "";
            $params["email"] = isset($this->basicInfo["email"]) ? $this->basicInfo["email"] : "";
            $params["address"] = isset($this->basicInfo["address"]) ? $this->basicInfo["address"] : "";
            $params["birthday"] = isset($this->basicInfo["birthday"]) ? $this->basicInfo["birthday"] : "";
            $params["is_push"] = isset($this->basicInfo["is_push"]) ? $this->basicInfo["is_push"] : 0;
            $params["remark"] = isset($this->basicInfo["remark"]) ? $this->basicInfo["remark"] : "";
            $customers = BusinessCardCustomer::getData(["id" => $this->id]);
            if(empty($customers)){
                $t->rollBack();
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"数据不存在");
            }
            $user = User::findOne($customers["user_id"]);
            if(empty($user)){
                $t->rollBack();
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"客户不存在");
            }
            $this->user_id = $customers["user_id"];
            //只有上级才能修改状态
            $parentId = $user->parent_id;
            if($parentId != \Yii::$app->user->id){
                $t->rollBack();
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"只有直推上级才能设置信息");
            }
            $data = ["id" => $this->id,"basic_info" => json_encode($params,JSON_UNESCAPED_UNICODE)];
            $result = BusinessCardCustomer::operateData($data);
            if ($result !== false) {
                //新增客户修改记录
                $this->remark = BusinessCardCustomerLog::$logTypes[BusinessCardCustomerLog::LOG_TYPE_UPDATE_INFO];
                $this->log_type = BusinessCardCustomerLog::LOG_TYPE_UPDATE_INFO;
                $result = $this->addCustomerLog();
                if ($result !== false) {
                    $t->commit();
                    return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"设置成功");
                }
            }
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"设置失败");
        }catch (\Exception $ex){
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"参数有误".$ex->getMessage());
        }
    }

    /**
     * 客户详情-AI分析
     * @return array
     */
    public function aiAnalysis(){
        $returnData = [];
        $returnData["interest_list"] = $this->interestStat();
        $returnData["hot_list"] = $this->goodsHotRank();
        $returnData["access_list"] = $this->accessDepth();
        $returnData["user_source"] = $this->userSource();
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $returnData);
    }

    /**
     * 客户兴趣统计
     * @return array
     */
    private function interestStat(){
        $returnData = $data = $params = [];
        $params = Common::selectTimeRange($params,$this->time_type);
        $params["user_id"] = $this->user_id;
        $params["group_by"] = "track_type";
        $fields = ['count(id) as total','track_type'];
        $list = BusinessCardTrackLog::getData($params,$fields);
        foreach ($list as $value){
            $data[$value["track_type"]] = $value;
        }
        foreach (BusinessCardTrackStat::$interests as $key => $val){
            if(!isset($data[$key])){
                $total = 0;
            }else{
                $total = intval($data[$key]["total"]);
                //如果类型是查看名片，将转发名片的次数也加上，因为都同属名片
                if($key == BusinessCardTrackLog::TRACK_TYPE_LOOK_CARD){
                    $total += isset($data[BusinessCardTrackLog::TRACK_TYPE_FORWARD_CARD]) ? $data[BusinessCardTrackLog::TRACK_TYPE_FORWARD_CARD] : 0;
                }
            }
            $statData["data"] = $total;
            $statData["name"] = $val." ".$total."次";
            $returnData[] = $statData;
        }
        return $returnData;
    }

    /**
     * 商品热度排行
     * @return array
     */
    private function goodsHotRank(){
        $hotParams = [];
        $hotParams["limit"] = 5;
        $hotParams["page"] = 1;
        $hotParams["user_id"] = $this->user_id;
        $hotParams["track_type"] = BusinessCardTrackLog::TRACK_TYPE_GOODS;
        $hotParams = Common::selectTimeRange($hotParams,$this->time_type);
        $returnData = BusinessCardTrackLogCommon::selectTrackLogHotRankList($hotParams);
        return $returnData["list"];
    }

    /**
     * 访问深度
     */
    private function accessDepth(){
        $returnData = $data = $params = $categories = $depthData = [];
        $params = Common::selectTimeRange($params,$this->time_type);
        $params["user_id"] = $this->user_id;
        $dateData = Common::getDayDate($this->time_type);
        $params["group_by"] = "access_at";
        $params["sort_key"] = "access_at";
        $params["sort_val"] = "asc";
        $params["track_type"] = array_keys(BusinessCardTrackStat::$interests);
        $fields = ['count(id) as total',"FROM_UNIXTIME( created_at, '%Y-%m-%d' ) AS access_at"];
        $list = BusinessCardTrackLog::getData($params,$fields);
        if(!empty($list)){
            foreach ($list as $value){
                $depthData[$value["access_at"]]["total"] = intval($value["total"]);
            }
        }
        foreach ($dateData as $val){
            $categories[] = date("m-d",strtotime($val));
            $data[] = isset($depthData[$val]) ? $depthData[$val]["total"] : 0;
        }
        $returnData["categories"] = $categories;
        $series = [];
        $series["name"] = "访问深度";
        $series["data"] = $data;
        $returnData["series"][] = $series;
        return $returnData;
    }

    /**
     * 用户来源
     * @return array
     */
    private function userSource(){
        $returnData = $data = $params = $categories = $sourceData = [];
        $params = Common::selectTimeRange($params,$this->time_type);
        $params["group_by"] = "source";
        $params["parent_id"] = $this->user_id;
        $fields = ['count(id) as total',"source"];
        $list = User::getData($params,$fields);
        foreach ($list as $value){
            $sourceData[$value["source"]]["total"] = intval($value["total"]);
        }
        foreach (User::$sources as $key => $source){
            $categories[] = $source;
            $data[] = isset($sourceData[$key]) ? $sourceData[$key]["total"] : 0;
        }
        $returnData["categories"] = $categories;
        $series = [];
        $series["name"] = "用户来源";
        $series["color"] = "#bc0100";
        $series["data"] = $data;
        $returnData["series"][] = $series;
        return $returnData;
    }

    /**
     * 修改客户状态
     * @return array
     * @throws \Exception
     */
    public function updateStatus(){
        if(empty($this->status) || empty($this->user_id)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
        }

        //不是新增线索状态时，才进行以下判断
        if($this->status != BusinessCardCustomer::STATUS_NEW_CLUE){
            $user = User::findOne($this->user_id);
            if(empty($user)){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"客户不存在");
            }
            //只有上级才能修改状态
            $parentId = $user->parent_id;
            if($parentId != \Yii::$app->user->id){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"只有直推才能添加商机");
            }
        }
        try{
            $businessCardCustomerLogCommon = new BusinessCardCustomerLogCommon();
            $paramsData = [];
            $paramsData["user_id"] = $this->user_id;
            $paramsData["status"] = $this->status;
            $paramsData["mall_id"] = \Yii::$app->mall->id;
            $businessCardCustomerLogCommon->updateCustomerInfo($paramsData);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功");
        }catch (\Exception $ex){
            $this->returnApiResultData(ApiCode::CODE_FAIL,$ex->getMessage());
        }
    }
}