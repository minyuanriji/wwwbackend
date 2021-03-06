<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户行为轨迹统计接口处理类
 * Author: zal
 * Date: 2020-07-06
 * Time: 14:30
 */

namespace app\plugins\business_card\forms\api;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\plugins\business_card\models\BusinessCardTag;
use app\plugins\business_card\models\BusinessCardTrackLog;
use app\plugins\business_card\models\BusinessCardTrackStat;

class BusinessCardTrackStatForm extends BaseModel
{
    public $page = 1;
    public $limit = 10;

    //客户用户id
    public $user_id;

    public $date;

    public function rules()
    {
        return [
            [['limit'], 'default', 'value' => 10],
            [['page','user_id'], 'integer'],
            [["date"],"string"]
        ]; // TODO: Change the autogenerated stub
    }

    /**
     * 行为列表
     * @param $flag 1=我的行为 2客户浏览历史
     * @return array
     */
    public function getList($flag = 1)
    {
        //查看的是客户的浏览历史
        if($flag == 2 && empty($this->user_id)){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
        }
        $params = $data = $returnData = [];
        $currentUserId = \Yii::$app->user->identity->id;
        $params["track_user_id"] = empty($this->user_id) ? $currentUserId : $this->user_id;
        $params["not_user_id"] = $params["track_user_id"];
        $params["page"] = $this->page;
        $params["limit"] = $this->limit;
        $params["look_time"] = $this->date;
        $params["user"] = 1;
        $params["sort_key"] = "updated_at";
        $fields = ['id','track_user_id','track_type','user_id','model_id','total','updated_at'];
        $list = BusinessCardTrackStat::getData($params,$fields);
        //$businessCardSetting = BusinessCardSetting::getData(\Yii::$app->mall->id);
        if(!empty($list["list"])){
            foreach ($list["list"] as &$value){
                $value["date"] = date("Y-m-d",$value["updated_at"]);
                $value["time"] = date("H:i",$value["updated_at"]);
                $total = $value["total"];
                $trackType = $value["track_type"];
                //对象名（名片、标签等）
                $trackName = isset(BusinessCardTrackStat::$trackTypeData[$value["track_type"]]) ? BusinessCardTrackStat::$trackTypeData[$value["track_type"]] : "";
                if($total == 1){
                    $text = "首次";
                }else{
                    $text = "第{$total}次";
                }
                //动作
                $actionName = "";
                if($trackType == BusinessCardTrackStat::TRACK_TYPE_LIKE_TAG){
                    $actionName = "点赞";
                    $tagData = BusinessCardTag::getData(["id" => $value["model_id"]]);
                    $trackName = !empty($tagData) ? "标签<{$tagData["name"]}>" : "标签";
                }else{
                    $actionName = BusinessCardTrackStat::$trackActions[$value["track_type"]];
                }
                $value["text"] = "{$text}";
                $value["type_name"] = $trackName;
                $value["action_name"] = $actionName;
                $value["user_data"]["nickname"] = !empty($value["user"]["nickname"]) ? $value["user"]["nickname"] : $value["user"]["mobile"];
                $value["user_data"]["avatar_url"] = $value["user"]["avatar_url"];
                unset($value["updated_at"],$value["user"]);
//            if(isset($data["date"]) && $value["date"] == $data["date"]){
//                $data["list"][] = $value;
//                $data["date"] = $value["date"];
//            }else{
//                $data["date"] = $value["date"];
//                $data["list"][] = $value;
//            }
                $returnData["list"][] = $value;
            }
        }else{
            $returnData["list"] = [];
        }
        $returnData["pagination"] = $this->getPaginationInfo($list["pagination"]);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $returnData);
    }

    /**
     * 创建
     * @return array
     */
    public function save(){
        $t = \Yii::$app->db->beginTransaction();
        try{
            $formData = [];
            $formData["user_id"] = \Yii::$app->user->identity->id;
            $formData["mall_id"] = \Yii::$app->mall->id;
            $formData["track_type"] = $this->track_type;
            $formData["track_user_id"] = $this->track_user_id;
            $formData["model_id"] = $this->model_id;
            $formData["remark"] = $this->remark;
            $formData["ip"] =  \Yii::$app->userIp;
            $result = BusinessCardTrackLog::operateData($formData);
            if ($result === false) {
                throw new \Exception("添加失败");
            }
            unset($formData["remark"],$formData["ip"]);
            //添加或更新统计数据
            $statData = $formData;
            $statData["total"] = 1;
            $trackStat = BusinessCardTrackStat::getData(["user_id" => $formData["user_id"],"track_user_id" => $formData["track_user_id"],
                "track_type" => $formData["track_type"],"is_one" => 1]);
            \Yii::error("plugin_business_card_track_stat ".var_export($trackStat,true));
            if(!empty($trackStat)){
                $statData["id"] = $trackStat["id"];
            }

            $result = BusinessCardTrackStat::operateData($statData);
            if ($result === false) {
                throw new \Exception("添加失败!");
            }
            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"成功");
        }catch (\Exception $ex){
            $t->rollBack();
            \Yii::error("BusinessCardTrackLog save error=".CommonLogic::getExceptionMessage($ex));
            return $this->returnApiResultData(999,CommonLogic::getExceptionMessage($ex));
        }

    }
}