<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片插件-处理名片行为轨迹公共类
 * Author: zal
 * Date: 2020-07-10
 * Time: 10:10
 */

namespace app\plugins\business_card\forms\common;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Goods;
use app\models\User;
use app\plugins\business_card\forms\api\BusinessCardTrackLogForm;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardTrackLog;
use app\plugins\business_card\models\BusinessCardTrackStat;
use yii\db\Exception;

class BusinessCardTrackLogCommon extends BaseModel
{

    /**
     * 添加行为轨迹公共方法
     * @param $data[track_user_id,model_id,track_type]
     * @return array
     */
    public function addTrackLogCommon($data){
        try{
            $form = new BusinessCardTrackLogForm();
            $track_user_id = $data["track_user_id"];
            if($track_user_id == \Yii::$app->user->id && $data["track_type"] != BusinessCardTrackLog::TRACK_TYPE_LIKE){
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"ok");
            }
            if(!empty($track_user_id)){
                $user = User::getOneUser(["id" => $track_user_id]);
                if(empty($user)){
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,"数据异常");
                }
            }
            $form->track_user_id = $track_user_id;
            $form->model_id = isset($data["model_id"]) ? $data["model_id"] : 0;
            $form->track_type = $data["track_type"];
            $form->remark = isset($data["remark"]) ? $data["remark"] : "";
            return $form->save();
        }catch (\Exception $ex){
            \Yii::error("BusinessCard addTrackLogCommon error ".CommonLogic::getExceptionMessage($ex));
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
        }
    }

    /**
     * 查看行为轨迹
     * @param $params[track_user_id,model_id,track_type]
     * @return array
     */
    public function selectTrackLog($params){
        try{
            $returnData = $data = [];
            $returnData["list"] = [];
            $params["mall_id"] = \Yii::$app->mall->id;
            $params["return_count"] = 1;
            $params["group_by"] = "user_id";
            //浏览人数
            $total = BusinessCardTrackLog::getData($params);
            unset($params["return_count"]);
            $params["user"] = 1;
            $params["trackUser"] = 1;
            //浏览记录
            $list = BusinessCardTrackLog::getData($params,["user_id","track_user_id","model_id","track_type"]);
            foreach ($list["list"] as $value){
                $data["user_id"] = intval($value["user_id"]);
                $data["track_user_id"] = intval($value["track_user_id"]);
                $data["model_id"] = intval($value["model_id"]);
                $data["track_type"] = intval($value["track_type"]);
                $businessCardInfo = Common::getBusinecardInfo(0,0,$value["user_id"]);
                $data["my_card_id"] = empty($businessCardInfo) ? 0 : intval($businessCardInfo["id"]);
                if(isset($value["user"])){
                    $user = $value["user"];
                    $data["user"]["nickname"] = empty($user["nickname"]) ? $user["mobile"] : $user["nickname"];
                    $data["user"]["avatar_url"] = empty($user["avatar_url"]) ? $user["avatar_url"] : $user["avatar_url"];
                }
                if(isset($value["trackUser"])){
                    $trackUser = $value["track_user"];
                    $data["track_user"]["nickname"] = empty($trackUser["nickname"]) ? $trackUser["mobile"] : $trackUser["nickname"];
                    $data["track_user"]["avatar_url"] = empty($trackUser["avatar_url"]) ? $trackUser["avatar_url"] : $trackUser["avatar_url"];
                }
                $returnData["list"][] = $data;
            }
            $returnData["total"] = $total;
            return $returnData;
        }catch (\Exception $ex){
            return [];
        }
    }

    /**
     * 添加行为轨迹
     * @param $trackUserId
     * @param $modelId
     * @param $trackType
     * @param $remark
     * @return array
     */
    public static function addTrackLog($trackUserId,$modelId,$trackType,$remark = ""){
        $data = [];
        $data["track_user_id"] = $trackUserId;
        $data["model_id"] = $modelId;
        $data["track_type"] = $trackType;
        $data["remark"] = $remark;
        $businessCardTrackLogCommon = new BusinessCardTrackLogCommon();
        return $businessCardTrackLogCommon->addTrackLogCommon($data);
    }

    /**
     * 是否存在数据
     * @param $trackUserId
     * @param $bcid
     * @return bool
     */
    public static function isExist($trackUserId,$bcid,$trackType = BusinessCardTrackLog::TRACK_TYPE_LIKE){
        $result = BusinessCardTrackLog::getData(["user_id" => \Yii::$app->user->id,"track_type" => $trackType,
            "track_user_id" => $trackUserId,"model_id" => $bcid,"is_one" => 1]);
        return $result;
    }

    /**
     * 查看轨迹对象-热度排行(行为轨迹统计表)
     * @param $params[track_user_id,model_id,track_type]
     * @return array
     */
    public static function selectTrackStatHotRank($params){
        try{
            $returnData = $data = [];
            $params["mall_id"] = \Yii::$app->mall->id;
            $params["group_by"] = "model_id";
            $params["sort_key"] = "total";
            $params["sort_val"] = "desc";
            $list = BusinessCardTrackStat::getData($params,['sum(total) as total',"model_id","track_type"]);
            foreach ($list["list"] as $value){
                $data["model_id"] = $value["model_id"];
                $trackType = $value["track_type"];
                $name = $image = "";
                $sales = 0;
                if($trackType == BusinessCardTrackLog::TRACK_TYPE_GOODS){
                    $goods = Goods::findOne($data["model_id"]);
                    if(!empty($goods)){
                        $name = $goods->goodsWarehouse->name;
                        $image = $goods->goodsWarehouse->cover_pic;
                        $sales = intval($goods->sales);
                    }
                }
                $data["name"] = $name;
                $data["image"] = $image;
                $data["sales"] = $sales;
                $data["total"] = intval($value["total"]);
                $returnData[] = $data;
            }
            return $returnData;
        }catch (\Exception $ex){
            return [];
        }
    }

    /**
     * 热度排行（行为轨迹表，如果有时间筛选调用该方法查热度排行）
     * @param $params
     * @return array
     */
    public static function selectTrackLogHotRankList($params){
        $returnData = $data = [];
        $fields = ['count(id) as total','track_type','model_id'];
        $params["mall_id"] = \Yii::$app->mall->id;
        $params["group_by"] = "model_id";
        $params["sort_key"] = "total";
        $params["sort_val"] = "desc";
        $list = BusinessCardTrackLog::getData($params,$fields);
        foreach ($list["list"] as $value){
            $data["model_id"] = $value["model_id"];
            $trackType = $value["track_type"];
            $name = $image = "";
            $sales = 0;
            if($trackType == BusinessCardTrackLog::TRACK_TYPE_GOODS){
                $goods = Goods::findOne($data["model_id"]);
                if(!empty($goods)){
                    $name = $goods->goodsWarehouse->name;
                    $image = $goods->goodsWarehouse->cover_pic;
                    $sales = intval($goods->sales);
                }
            }
            $data["position_name"] = "";
            if($trackType == BusinessCardTrackLog::TRACK_TYPE_FORWARD_CARD || $trackType == BusinessCardTrackLog::TRACK_TYPE_LOOK_CARD){
                $businessCards = Common::getBusinecardInfo(1,$data["model_id"]);
                if(!empty($businessCards)){
                    $name = $businessCards["user_data"]["nickname"];
                    $image = $businessCards["user_data"]["avatar_url"];
                    $sales = 0;
                    $data["position_name"] = $businessCards["user_data"]["position_name"];
                }
            }
            $data["name"] = $name;
            $data["image"] = $image;
            $data["sales"] = $sales;
            $data["total"] = intval($value["total"]);
            $returnData[] = $data;
        }
        $list["list"] = $returnData;
        return $list;
    }
}
