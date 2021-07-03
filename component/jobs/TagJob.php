<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 标签任务类
 * Author: zal
 * Date: 2020-08-03
 * Time: 19:16
 */

namespace app\component\jobs;

use app\forms\common\tag\TagCommon;
use app\logic\UserLogic;
use app\models\CommonOrder;
use app\models\GoodsCatRelation;
use app\models\GoodsCollect;
use app\models\Mall;
use app\models\ObjectTag;
use app\models\OrderDetail;
use app\models\Tag;
use app\plugins\business_card\models\BusinessCardTrackLog;
use app\plugins\business_card\models\BusinessCardTrackStat;
use yii\base\Component;
use yii\queue\JobInterface;

class TagJob extends Component implements JobInterface
{
    public $user_id;
    public $mall_id;
    public $cat_id;
    public $action;
    public $type;

    public function execute($queue)
    {
        try {
            \Yii::warning('----TagJob start----');
            $mall = Mall::findOne(['id' => $this->mall_id]);
            \Yii::$app->setMall($mall);
            if(!empty($this->user_id)){
                $this->tagHandle();
            }else{
                \Yii::warning('----TagJob user not exist ----');
            }
            \Yii::warning('----TagJob end----');
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
    }

    /**
     * 标签核心处理
     */
    private function tagHandle(){
        $params = [];
        $params["cat_id"] = $this->cat_id;
        $params["mall_id"] = $this->mall_id;
        $params["type"] = $this->type;
        $tagData = Tag::getData($params);
        foreach ($tagData as $val){
            $type = $val["type"];
            $condition = $val["condition"];
            $conditionData = json_decode($condition,true);
            $conditionData = isset($conditionData[$type]) ? $conditionData[$type] : [];
            if(empty($conditionData)){
                continue;
            }
            $result = $this->tagConditionHandle($conditionData,$type);
            if($result == true){
                $addTagData = [];
                $addTagData["mall_id"] = $this->mall_id;
                $addTagData["object_id"] = $this->user_id;
                $addTagData["tag_id"] = $val["id"];
                $addTagData["cat_id"] = $this->cat_id;
                TagCommon::addObjectTag($addTagData);
            }
        }
    }

    /**
     * 检测标签条件是否满足
     * @param $condition
     * @param $type
     * @return bool
     */
    private function tagConditionHandle($condition,$type){
        \Yii::warning("tagConditionHandle condition = ".var_export($condition,true));
        //$type = empty($this->type) ? $type : $this->type;
        $verify = false;
        $params = [];
        $commonOrderModel = new CommonOrder();
        $commonOrderModel->mall_id = $this->mall_id;
        //价值分层
        if($type == Tag::TYPE_VALUE_SLICE){
            $params["user_id"] = $this->user_id;
            $params["is_pay"] = 1;
            $params["mall_id"] = $this->mall_id;
            foreach ($condition as $kk => $vv){
                $use = $vv["use"];
                $min = $vv["min"];
                $max = $vv["max"];
                if($use === true){
                    if($kk == "pay_count"){
                        $orderCount = $commonOrderModel->getList($params, "count");
                        \Yii::warning("tagConditionHandle condition {$type} order_count = {$orderCount} ");
                        if(($min <= $orderCount && $max >= $orderCount) || $orderCount >= $max){
                            $verify = true;
                        }else{
                            $verify = false;
                        }
                    }
                    if($kk == "pay_money"){
                        $orderTotal = $commonOrderModel->getList($params, "sum");
                        \Yii::warning("tagConditionHandle condition {$type} order_total = {$orderTotal} ");
                        if(($min <= $orderTotal && $max >= $orderTotal) || $orderTotal >= $max){
                            $verify = true;
                        }else{
                            $verify = false;
                        }
                    }
                }
            }
        }else if($type == Tag::TYPE_LIFE_CYCLE){
            //生命周期
            $params["user_id"] = $this->user_id;
            $params["is_pay"] = 1;
            $params["mall_id"] = $this->mall_id;
            foreach ($condition as $kk => $vv){
                $use = $vv["use"];
                $num = isset($vv["num"]) ? $vv["num"] : 0;
                if($use === true){
                    if($kk == "new_user"){
                        $orderCount = $commonOrderModel->getList($params, "count");
                        if(empty($orderCount)){
                            $verify = true;
                        }
                    }
                    if($kk == "active_user"){

                    }
                    if($kk == "silence_user"){

                    }
                    if($kk == "lose_user"){

                    }
                    if($kk == "after_purchase_user"){
                        //购买同一商品
                        $fields = ["sum(od.num) as total","od.goods_id"];
                        $params["group_by"] = "goods_id";
                        $params["order"] = 1;
                        $sameOrderList = OrderDetail::getSameCatsGoodsOrderTotal($params,$fields);
                        $total = 0;
                        if(!empty($sameOrderList)){
                            foreach ($sameOrderList as $value){
                                $total += $value["total"];
                            }
                        }
                        if($total >= $num){
                            $verify = true;
                        }
                    }
                }
            }
        }else if($type == Tag::TYPE_MARKET_PREFERENCE){
            //营销偏好
            $params["user_id"] = $this->user_id;
            $params["is_pay"] = 1;
            $params["mall_id"] = $this->mall_id;
            foreach ($condition as $kk => $vv){
                $use = $vv["use"];
                $num = isset($vv["num"]) ? $vv["num"] : 0;
                if($use === true){
                    if($kk == "direct_drive_num"){
                        $searchParams = [];
                        $searchParams["flag"] = 1;
                        $searchParams["user_id"] = $this->user_id;
                        $searchParams["mall_id"] = $this->mall_id;
                        $directNum = UserLogic::getUserTeamPushList($searchParams,"count");
                        $directNum = intval($directNum);
                        if($directNum >= $num){
                            $verify = true;
                        }
                    }
                    if($kk == "card_praise_num"){
                        $cardSearchParams = [];
                        $cardSearchParams["track_type"] = BusinessCardTrackStat::TRACK_TYPE_LIKE;
                        $cardSearchParams["track_user_id"] = $this->user_id;
                        $cardSearchParams["mall_id"] = $this->mall_id;
                        $cardSearchParams["return_sum"] = "total";
                        $cardLikeNum = BusinessCardTrackStat::getData($cardSearchParams);
                        $cardLikeNum = intval($cardLikeNum);
                        if($cardLikeNum >= $num){
                            $verify = true;
                        }
                    }
                }
            }
        }else if($type == Tag::TYPE_BEHAVIOR_PREFERENCE){
            //行为偏好
            $params["user_id"] = $this->user_id;
            $params["is_pay"] = 1;
            $params["mall_id"] = $this->mall_id;
            foreach ($condition as $kk => $vv){
                $use = $vv["use"];
                $num = isset($vv["num"]) ? $vv["num"] : 0;
                if($use === true){
                    //购买同类型商品
                    if($kk == "buy_kind_goods"){
                        $goods_warehouse_ids = GoodsCatRelation::find()->where(["is_delete" => 1])->groupBy("goods_warehouse_id")->select(["goods_warehouse_id"])->column("goods_warehouse_id");
                        $fields = ["count(od.id) as total","od.goods_id"];
                        $params["goods_id"] = $goods_warehouse_ids;
                        $sameOrderList = OrderDetail::getSameCatsGoodsOrderTotal($params,$fields);
                        $total = 0;
                        if(!empty($sameOrderList)){
                            foreach ($sameOrderList as $value){
                                $total += $value["total"];
                            }
                        }
                        if($total >= $num){
                            $verify = true;
                        }
                    }else if($kk == "collect_kind_goods"){
                        //收藏同类型商品
                        $goods_warehouse_ids = GoodsCatRelation::find()->where(["is_delete" => 1])->groupBy("goods_warehouse_id")->select(["goods_warehouse_id"])->column("goods_warehouse_id");
                        $fields = ["count(id) as total","goods_id"];
                        $params["goods_id"] = $goods_warehouse_ids;
                        $sameOrderList = GoodsCollect::getSameCatsGoodsCollectTotal($params,$fields);
                        $total = 0;
                        if(!empty($sameOrderList)){
                            foreach ($sameOrderList as $value){
                                $total += $value["total"];
                            }
                        }
                        if($total >= $num){
                            $verify = true;
                        }
                    }else if($kk == "search_kind_goods"){

                    }else if($kk == "visit_kind_page"){
                        //访问同一页面次数
                        $params["group_by"] = "track_type";
                        $params["track_type"] = array_keys(BusinessCardTrackStat::$interests);
                        $fields = ['count(id) as total'];
                        $list = BusinessCardTrackLog::getData($params,$fields);
                        $total = 0;
                        if(!empty($list)){
                            foreach ($list as $value){
                                $total += $value["total"];
                            }
                        }
                        if($total >= $num){
                            $verify = true;
                        }
                    }else if($kk == "collect_goods"){
                        //收藏商品件数
                        $fields = ["count(id) as total","goods_id"];
                        $params["is_one"] = 1;
                        $collectData = GoodsCollect::getSameCatsGoodsCollectTotal($params,$fields);
                        $total = 0;
                        if(!empty($collectData)){
                            $total = $collectData["total"];
                        }
                        if($total >= $num){
                            $verify = true;
                        }
                    }else if($kk == "like_num"){
                        //点赞次数
                        $params["return_count"] = 1;
                        $params["track_type"] = BusinessCardTrackStat::TRACK_TYPE_LIKE;
                        $total = 0;
                        $likesTotal = BusinessCardTrackLog::getData($params);
                        if(!empty($likesTotal)){
                            $total = $likesTotal;
                        }
                        if($total >= $num){
                            $verify = true;
                        }
                    }else if($kk == "share_num"){
                        //分享次数
                        $params["return_count"] = 1;
                        $params["not_track_user_id"] = 1;
                        $params["track_type"] = [BusinessCardTrackLog::TRACK_TYPE_GOODS,BusinessCardTrackLog::TRACK_TYPE_FORWARD_CARD];
                        $total = 0;
                        $likesTotal = BusinessCardTrackLog::getData($params);
                        if(!empty($likesTotal)){
                            $total = $likesTotal;
                        }
                        if($total >= $num){
                            $verify = true;
                        }
                    }
                }
            }
        }
        return $verify;
    }
}
