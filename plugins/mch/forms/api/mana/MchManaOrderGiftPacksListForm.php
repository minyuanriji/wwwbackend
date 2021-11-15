<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;
use app\models\Goods;
use app\models\Store;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;

class MchManaOrderGiftPacksListForm extends BaseModel {

    public $page;
    public $status; //状态：paid(待使用)，finished（已结束）,unpaid（待付款）
    public $mch_id;

    public function rules(){
        return [
            [['status'], 'required'],
            [['page', 'mch_id'], 'integer'],
            [['status'], 'string']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = GiftpacksOrderItem::find()->alias("goi")
                ->innerJoin(["go" => GiftpacksOrder::tableName()], "goi.order_id=go.id")
                ->innerJoin(["gi" => GiftpacksItem::tableName()], "gi.id=goi.pack_item_id")
                ->innerJoin(["g" => Goods::tableName()], "g.id=gi.goods_id")
                ->innerJoin(["gf" => Giftpacks::tableName()], "gf.id=gi.pack_id")
                ->innerJoin(["s" => Store::tableName()], "s.id=gi.store_id")
                ->innerJoin(["m" => Mch::tableName()], "m.id=s.mch_id")
                ->innerJoin(["u" => User::tableName()], "u.id=go.user_id");

            $query->andWhere(["m.id" => $this->mch_id ? $this->mch_id : MchAdminController::$adminUser['mch_id']]);

            //状态查询
            if($this->status == "paid"){ //待使用
                $query->andWhere([
                    "AND",
                    ["go.pay_status" => "paid"],
                    "(goi.max_num=0 OR goi.current_num>0) AND (goi.expired_at=0 OR goi.expired_at>='".time()."')"
                ]);
            }elseif($this->status == "finished"){ //已结束
                $query->andWhere([
                    "AND",
                    ["go.pay_status" => "paid"],
                    "(goi.max_num<>0 AND goi.current_num=0) OR (goi.expired_at<>0 AND goi.expired_at<'".time()."')"
                ]);
            }elseif($this->status == "unpaid"){ //待付款
                $query->andWhere(["go.pay_status" => "unpaid"]);
            }

            $selects = ["go.id as order_id", "go.order_sn", "go.order_price", "go.created_at", "go.updated_at", "go.pay_status",
                "go.pay_at", "go.pay_price", "goi.id as order_item_id", "goi.max_num", "goi.current_num", "goi.expired_at", "gf.title as pack_name",
                "gi.name as item_name", "gi.cover_pic item_pic", "gi.item_price", "g.price as goods_price",
                "u.nickname", "u.mobile", "u.avatar_url", "u.id as user_id", "m.id as mch_id", "s.id as store_id",
                "s.name as store_name"
            ];
            $list = $query->orderBy("go.id DESC")->select($selects)->asArray()->page($pagination, 10, $this->page)->all();
            if($list){
                $orderItemIds = [];
                foreach($list as &$item){
                    $remarkArr = [];
                    $remarkArr[] = $item['max_num'] == 0 ? "不限次数" : "剩余".intval($item['current_num'])."次";
                    if($item['expired_at'] == 0){
                        $remarkArr[] = "永久有效";
                    }else{
                        $seconds = max(0, intval($item['expired_at']) - time());
                        if($seconds > 0){
                            if($seconds > 3600 * 24){
                                $days =  intval($seconds/(3600 * 24));
                                $remarkArr[] = "{$days}天后到期";
                            }elseif($seconds > 3600){
                                $hours =  intval($seconds/(3600));
                                $remarkArr[] = "{$hours}小时后到期";
                            }elseif($seconds > 60){
                                $mins =  intval($seconds/(60));
                                $remarkArr[] = "{$mins}分钟后到期";
                            }else{
                                $remarkArr[] = "{$seconds}秒后到期";
                            }
                        }else{
                            $remarkArr[] = "已过期";
                        }
                    }
                    $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
                    $item['remarks'] = implode("，", $remarkArr);
                    $orderItemIds[] = $item['order_item_id'];
                }
                //获取核销记录
                $clerkDatas = [];
                $rows = ClerkData::find()->andWhere([
                    "AND",
                    ["status" => 1],
                    ["source_type" => "giftpacks_order_item"],
                    ["IN", "source_id", $orderItemIds]
                ])->asArray()->all();
                if($rows){
                    foreach($rows as $row){
                        $row['updated_at'] = date("Y-m-d H:i:s", $row['updated_at']);
                        $row['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                        $clerkDatas[$row['source_id']][] = $row;
                    }
                }
                foreach($list as &$item){
                    $item['clerk_log'] = isset($clerkDatas[$item['order_item_id']]) ? $clerkDatas[$item['order_item_id']] : [];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list,
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){

            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}