<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销插件-处理分销分佣公共类
 * Author: zal
 * Date: 2020-05-29
 * Time: 18:10
 */

namespace app\plugins\business_card\forms\common;

use app\core\BasePagination;
use app\helpers\ArrayHelper;
use app\logic\AppConfigLogic;
use app\logic\UserLogic;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\MallSetting;
use app\models\PriceLog;
use app\models\User;
use app\models\UserParent;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardCustomer;
use app\plugins\business_card\models\BusinessCardCustomerLog;
use app\plugins\business_card\models\BusinessCardSetting;
use app\plugins\business_card\models\BusinessCardTag;
use app\plugins\business_card\models\BusinessCardTrackLog;
use app\plugins\business_card\models\BusinessCardTrackStat;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionLog;
use app\plugins\distribution\models\DistributionOrder;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\Plugin;
use app\plugins\sign_in\forms\BaseModel;

class Common extends BaseModel
{
    public $config;
    public $sign;

    public static $instance;


    /**
     * 获取公共类
     * @param $mall
     * @return Common
     * @throws \Exception
     */
    public static function getCommon($mall)
    {
        if (self::$instance) {
            return self::$instance;
        }
        $form = new Common();
        $form->mall = $mall;
        self::$instance = $form;


        return $form;
    }

    /**
     * 获取配置
     * @return DistributionSetting|null
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = DistributionSetting::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new DistributionSetting();
            $config->mall_id = $this->mall->id;
        }
        $this->config = $config;

        return $config;
    }

    /**
     * 组装时间范围内的查询条件
     * @param $params
     * @param $timeType
     * @return array
     */
    public static function selectTimeRange($params,$timeType){
        $params["filter_time_start"] = "";
        $params["filter_time_end"] = "";
        if($timeType > -1){
            switch ($timeType){
                case 0:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-1 day"));
                    $filterTimeEnd = date("Y-m-d 23:59:59",strtotime("-1 day"));
                    break;
                case 1:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-6 day"));
                    $filterTimeEnd = date("Y-m-d 23:59:59");
                    break;
                case 2:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-14 day"));
                    $filterTimeEnd = date("Y-m-d 23:59:59");
                    break;
                case 3:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-29 day"));
                    $filterTimeEnd = date("Y-m-d 23:59:59");
                    break;
                default:
                    $filterTimeStart = "";
                    $filterTimeEnd = "";
                    break;
            }
            $params["filter_time_start"] = strtotime($filterTimeStart);
            $params["filter_time_end"] = strtotime($filterTimeEnd);
        }
        return $params;
    }

    /**
     * 获取所查询天数对应的日期
     * @param $time_type
     * @return array
     */
    public static function getDayDate($time_type){
        $returnData = [];
        if($time_type > -1){
            switch ($time_type){
                case 0:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-1 day"));
                    $day = 1;
                    break;
                case 1:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-6 day"));
                    $day = 7;
                    break;
                case 2:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-14 day"));
                    $day = 15;
                    break;
                default:
                    $filterTimeStart = date("Y-m-d 00:00:00",strtotime("-29 day"));
                    $day = 30;
                    break;
            }
            for ($i=0;$i<$day;$i++){
                $timeStart = date("Y-m-d",strtotime("+$i day",strtotime($filterTimeStart)));
                $returnData[$i] = date("Y-m-d",strtotime($timeStart));
            }
        }
        return $returnData;
    }

    /**
     * 获取当前用户名片数据
     * @param simple 只取精简数据1精简0完整
     * @param $bcid
     * @param $userId
     * @return \app\models\BaseActiveQuery|array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]|null
     */
    public static function getBusinecardInfo($simple = 0,$bcid = 0,$userId = 0){
        $userParams = $returnData = [];
        if(empty($bcid)){
            $userParams["user_id"] = empty($userId) ? \Yii::$app->user->id : $userId;
            $userParams["mall_id"] = \Yii::$app->mall->id;
            $userParams["is_one"] = 1;
        }else{
            $userParams["id"] = $bcid;
        }
        $busineCardData = BusinessCard::getData($userParams);
        if($simple == 1){
            $returnData["user_data"]["id"] = $busineCardData["id"];
            $returnData["user_data"]["user_id"] = $busineCardData["user_id"];
            $returnData["user_data"]["nickname"] = isset($busineCardData["user"]["nickname"]) && !empty($busineCardData["user"]["nickname"]) ? $busineCardData["user"]["nickname"] : $busineCardData["user"]["mobile"];
            $returnData["user_data"]["avatar_url"] = $busineCardData["user"]["avatar_url"];
            $returnData["user_data"]["position_name"] = isset($busineCardData["position"]["name"]) ? $busineCardData["position"]["name"] : "";
            $returnData["user_data"]["wechat_qrcode"] = $busineCardData["wechat_qrcode"];
        }else{
            $returnData = $busineCardData;
        }
        return $returnData;
    }

    /**
     * 获取雷达统计数据（主要应用于名片中心和boss雷达概括页）
     * @param $returnData
     * @param $params
     * @param $userId boss雷达页面时，是部门下所有员工
     * @param $allUserIds boss雷达页面，部门下所有用户
     * @return mixed
     */
    public static function getRadarStatInfo($returnData,$params,$userId,$allUserIds){
        if(empty($userId)){
            $returnData["new_client_total"] = 0;
            $returnData["browse_total"] = 0;
            $returnData["new_clue_total"] = 0;
            $returnData["team_order_count"] = 0;
            $returnData["team_order_total"] = 0;
            $returnData["order_user_total"] = 0;
            $returnData["intent_total"] = 0;
            $returnData["compare_total"] = 0;
            $returnData["clinch_total"] = 0;
            return $returnData;
        }

        //统计新增客户数
        $params["operate_id"] = is_array($userId) ? $allUserIds : $userId;
        $params["user_type"] = 0;
        $fields = ['count(id) as total','track_type'];
        $newClientTotal = BusinessCardCustomer::getData($params,$fields);
        $returnData["new_client_total"] = intval($newClientTotal);
        unset($params["user_type"]);

        //统计浏览量
        $params["user_id"] = is_array($userId) ? $allUserIds : $userId;
        $params["track_type"] = array_keys(BusinessCardTrackStat::$interests);
        $browseTotal = BusinessCardTrackLog::getData($params);
        $returnData["browse_total"] = intval($browseTotal);

        unset($params["track_type"]);

        //订单数
        $childList = $allUserIds;
        $params["is_pay"] = 1;
        $orderStatData = UserLogic::getUserTeamOrderStatInfo($childList,$params);
        $teamOrderCount = intval($orderStatData["team_order_count"]);
        $returnData["team_order_count"] = $teamOrderCount;
        //下单总额
        $teamOrderTotal = intval($orderStatData["team_order_total"]);
        $returnData["team_order_total"] = $teamOrderTotal;

        //下单人数
        $orderUserNum = UserLogic::getUseTeamOrderPeopleTotal($childList,$params);
        $returnData["order_user_total"] = intval($orderUserNum);

        unset($params["is_pay"],$params["user_id"]);

        //新增线索
        $params["log_type"] = BusinessCardCustomerLog::LOG_TYPE_NEW_CLUE;
        $newClueTotal = BusinessCardCustomerLog::getData($params);
        $returnData["new_clue_total"] = intval($newClueTotal);

        //意向客户
        $params["user_type"] = BusinessCardCustomer::USER_TYPE_INTENT;
        $intentTotal = BusinessCardCustomer::getData($params);
        $returnData["intent_total"] = intval($intentTotal);

        //比较客户
        $params["user_type"] = BusinessCardCustomer::USER_TYPE_COMPARE;
        $compareTotal = BusinessCardCustomer::getData($params);
        $returnData["compare_total"] = intval($compareTotal);

        //待成交客户
        $params["user_type"] = BusinessCardCustomer::USER_TYPE_WAIT_CLINCH;
        $clinchTotal = BusinessCardCustomer::getData($params);
        $returnData["clinch_total"] = intval($clinchTotal);
        return $returnData;
    }

    /**
     * 获取名片标签
     * @param $detail
     * @param $isDiy 1自定义0自动
     * @return mixed
     */
    public static function getBusinessCardTag($detail,$isDiy = BusinessCardTag::IS_DIY_YES){
        $returnData = $data = [];
        $tagList = isset($detail["tag"]) && !empty($detail["tag"]) ? $detail["tag"] : [];
        if(!empty($tagList)){
            $tagList = ArrayHelper::toArray($tagList);
            foreach ($tagList as $value){
                if($value["is_diy"] == $isDiy){
                    $data["id"] = $value["id"];
                    $data["name"] = $value["name"];
                    $data["likes"] = intval($value["likes"]);
                    $data["is_like"] = Common::isLike(\Yii::$app->user->id,$value["id"],"tag");
                    $returnData[] = $data;
                }
            }
        }
        $detail["tag"] = $returnData;
        return $detail;
    }

    /**
     * 检测是否点赞
     * @param $userId
     * @param $id
     * @param $sign
     * @param int $duration
     * @return int
     */
    public static function checkIsLike($userId,$id,$sign,$duration = 0){
        $key = "like_{$sign}_{$userId}_$id";
        //不存在说明是点赞行为，否则是取消点赞
        if(\Yii::$app->cache->get($key)){
            \Yii::$app->cache->delete($key);
            $num = -1;
        }else{
            \Yii::$app->cache->set($key, true,$duration);
            $num = 1;
        }
        return $num;
    }

    /**
     * @param $userId
     * @param $id
     * @param $sign
     * @param int $duration
     * @return int
     */
    public static function isLike($userId,$id,$sign,$duration = 0){
        $key = "like_{$sign}_{$userId}_$id";
        //不存在说明是点赞行为，否则是取消点赞
        if(\Yii::$app->cache->get($key)){
            $is_like = true;
        }else{
            $is_like = false;
        }
        return $is_like;
    }

    public static function getDefault()
    {
        $urlPrefix = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl .
            '/statics/img/mall/poster/';
        return [
            'business_card' => [
                'bg_pic' => [
                    'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/mall/poster_bg.png',
                    'is_show' => '1',
                ],
                'head' => [
                    'is_show' => '1',
                    'size' => 60,
                    'top' => 10,
                    'left' => 10,
                    'file_type' => 'image',
                ],
                'qr_code' => [
                    'is_show' => '1',
                    'size' => 120,
                    'top' => 150,
                    'left' => 127,
                    'type' => '1',
                    'file_type' => 'image',
                ],
                'name' => [
                    'is_show' => '1',
                    'font' => 20,
                    'top' => 30,
                    'left' => 80,
                    'color' => '#000',
                    'file_type' => 'text',
                ],
            ],
            'footprint' => [
                'bg_pic' => [
                    'url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/statics/img/app/footprint/footprint_poster_bg.png',
                    'is_show' => '1',
                ],
                'qr_code' => [
                    'is_show' => '1',
                    'size' => 160,
                    'top' => 1140,
                    'left' => 540,
                    'type' => '1',
                    'file_type' => 'image',
                ],
                'text_one' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 380,
                    'left' => 124,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '1',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_1' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 380,
                    'left' => 148,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '.您在本店共购买',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_2' => [
                    'is_show' => '1',
                    'font' => 36,
                    'top' => 443,
                    'left' => 124,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '590',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_3' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 455,
                    'left' => 295,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '件商品',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_two' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 535,
                    'left' => 124,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '2',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_4' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 535,
                    'left' => 148,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '.您在本店共消费',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_5' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 600,
                    'left' => 124,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '￥',
//                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_6' => [
                    'is_show' => '1',
                    'font' => 36,
                    'top' => 588,
                    'left' => 157,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '888888',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_three' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 677,
                    'left' => 124,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '3',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
                'text_7' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 677,
                    'left' => 148,
                    'color' => '#252525',
                    'file_type' => 'text',
                    'text' => '.您在本店最高一次消费达',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_8' => [
                    'is_show' => '1',
                    'font' => 24,
                    'top' => 742,
                    'left' => 124,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '￥',
//                    'font_path' => \Yii::$app->basePath . '/web/statics/font/hanyicuyuanti.ttf',
                ],
                'text_9' => [
                    'is_show' => '1',
                    'font' => 36,
                    'top' => 730,
                    'left' => 157,
                    'color' => '#FF4544',
                    'file_type' => 'text',
                    'text' => '168888',
                    'font_path' => \Yii::$app->basePath . '/web/statics/font/DIN-Medium.otf',
                ],
            ],
        ];
    }

    /**
     * 获取名片海报
     * @return array
     */
    public static function getBusinessCardPoster(){
        $returnData = [];
        $businessCardData = BusinessCardSetting::find()->where(['mall_id' => \Yii::$app->mall->id,'key' => BusinessCardSetting::POSTER, 'is_delete' => 0])->asArray()->one();
        if(!empty($businessCardData)){
            $returnData = json_decode($businessCardData["value"],true);
        }else{
            $returnData = (new AppConfigLogic())->poster([], self::getDefault());
        }
        return $returnData;
    }
}
