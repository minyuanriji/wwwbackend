<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片定时任务
 * Author: zal
 * Date: 2020-07-28
 * Time: 15:16
 */

namespace app\commands;

use app\logic\UserLogic;
use app\models\CommonOrder;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardAiAnalysis;
use app\plugins\business_card\models\BusinessCardCustomer;
use app\plugins\business_card\models\BusinessCardTrackLog;
use yii\console\Controller;

class BusinessCardController extends Controller
{

    public function actionIndex()
    {
        $successNum = $existNum = $errorNum = 0;
        $successData = $errorData = [];
        $params = [];
        $params["status"] = BusinessCard::STATUS_ON;
        $list = BusinessCard::getData($params);
        foreach ($list as $value){
            $userId = $value["user_id"];
            $params = [];
            $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-1 day"));
            $filterTimeStart = strtotime($filterTimeStart);
            $filterTimeEnd = date("Y-m-d 23:59:59",strtotime("-1 day"));
            $filterTimeEnd = strtotime($filterTimeEnd);
            $year = date("Y",$filterTimeStart);
            $month = date('m',$filterTimeStart);
            $day = date('d',$filterTimeStart);
            $params["filter_time_start"] = $filterTimeStart;
            $params["filter_time_end"] = $filterTimeEnd;
            $params["user_id"] = $userId;
            $params["track_type"] = BusinessCardTrackLog::TRACK_TYPE_FORWARD_CARD;
            $params["return_count"] = 1;

            //是否重复
            $isExistParams = [];
            $isExistParams["user_id"] = $userId;
            $isExistParams["year"] = $year;
            $isExistParams["month"] = $month;
            $isExistParams["day"] = $day;
            $isExistParams["return_count"] = 1;
            $result = BusinessCardAiAnalysis::getData($isExistParams);
            if(!empty($result)){
                $existNum++;
                continue;
            }

            //销售主动值
            $sales_active = BusinessCardTrackLog::getData($params);
            $sales_active = empty($sales_active) ? 0 : $sales_active;
            //成交能力
            $dealParams = [];
            $userIds = UserLogic::getAllChildIdsList("id",$userId,$value["mall_id"]);
            $dealParams["is_pay"] = CommonOrder::STATUS_IS_PAY;
            $dealParams["user_id"] = $userIds;
            $dealParams["return_count"] = 1;
            $dealParams["filter_time_start"] = $filterTimeStart;
            $dealParams["filter_time_end"] = $filterTimeEnd;
            $deal_ability = CommonOrder::getData($dealParams);
            $deal_ability = empty($deal_ability) ? 0 : $deal_ability;
            //获客能力
            $abilityParams = [];
            $abilityParams["operate_id"] = $userId;
            $abilityParams["status"] = BusinessCardCustomer::STATUS_NEW_CLUE;
            $abilityParams["filter_time_start"] = $filterTimeStart;
            $abilityParams["filter_time_end"] = $filterTimeEnd;
            $abilityParams["return_count"] = 1;
            $customers_ability = BusinessCardCustomer::getData($abilityParams);
            $customers_ability = empty($customers_ability) ? 0 : $customers_ability;
            unset($params["user_id"]);
            //个人魅力值
            $params["track_user_id"] = $userId;
            $params["track_type"] = BusinessCardTrackLog::TRACK_TYPE_LOOK_CARD;
            $personal_appeal = BusinessCardTrackLog::getData($params);
            $personal_appeal = empty($personal_appeal) ? 0 : $personal_appeal;
            //官网推广值
            $params["track_type"] = BusinessCardTrackLog::TRACK_TYPE_MALL_INDEX;
            $website_promote = BusinessCardTrackLog::getData($params);
            $website_promote = empty($website_promote) ? 0 : $website_promote;
            //产品推广值
            $params["track_type"] = BusinessCardTrackLog::TRACK_TYPE_GOODS;
            $goods_promote = BusinessCardTrackLog::getData($params);
            $goods_promote = empty($goods_promote) ? 0 : $goods_promote;

            //总数
            $total = intval($sales_active) + intval($website_promote) + intval($goods_promote) +
                intval($deal_ability) + intval($customers_ability) + intval($personal_appeal);
            //平均值
            $average = $total / 6;
            $average = number_format($average,2);

            //新增ai分析记录
            $data = [];
            $data["user_id"] = $userId;
            $data["mall_id"] = $value["mall_id"];
            $data["sales_active"] = $sales_active;
            $data["website_promote"] = $website_promote;
            $data["goods_promote"] = $goods_promote;
            $data["deal_ability"] = $deal_ability;
            $data["customers_ability"] = $customers_ability;
            $data["personal_appeal"] = $personal_appeal;
            $data["average"] = $average;
            $data["total"] = $total;
            $data["year"] = $year;
            $data["month"] = $month;
            $data["day"] = $day;
            $result = BusinessCardAiAnalysis::operateData($data);
            if($result !== false){
                $successNum++;
                $successData[] = $data;
            }else{
                $errorNum++;
                $errorData[] = $data;
            }
        }
        echo "success:{$successNum},fail:{$errorNum};exist:{$existNum}";
        echo "\n successData:";
        print_r($successData);
        echo "\n errorData:";
        print_r($errorData);
        //\Yii::warning("command\BusinessCardController successData=".var_export($successData,true)." errorData=".var_export($errorData,true));
    }
}
