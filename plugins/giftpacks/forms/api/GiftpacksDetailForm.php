<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseActiveRecord;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPackItem;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class GiftpacksDetailForm extends BaseModel{

    public $pack_id;

    public function rules(){
        return [
            [['pack_id'], 'required']
        ];
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            $detail = static::detail($giftpacks);

            //如果支持拼单
            $groupList = $joinInfo = [];
            if($giftpacks->group_enable){
                //获取最新的两条拼单记录
                $groupList = static::newestGroupLog($giftpacks);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail'     => $detail,
                    'group_list' => $groupList
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    //大礼包详情
    public static function detail(Giftpacks $giftpacks){
        $detail = $giftpacks->getAttributes();
        $detail['item_count'] = static::getItemCount($giftpacks);
        $detail['sold_num'] = static::soldNum($giftpacks);
        return $detail;
    }

    //获取拼单记录
    public static function newestGroupLog(Giftpacks $giftpacks, $num = 2){
        $query = GiftpacksGroup::find()->alias("gg")
                    ->innerJoin(["u" => User::tableName()], "u.id=gg.user_id")
                    ->orderBy("gg.updated_at DESC");
        $query->andWhere([
            "AND",
            ["gg.status" => "sharing"],
            ["gg.pack_id" => $giftpacks->id],
            [">", "gg.expired_at", time()],
            [">", "gg.need_num", "gg.user_num"]
        ]);
        $selects = ["gg.id", "gg.need_num", "gg.user_num", "gg.expired_at",
            "gg.user_id", "u.nickname", "u.avatar_url"
        ];
        $query->select($selects);
        $groupLogs = $query->asArray()->limit($num)->all();
        if($groupLogs){
            foreach($groupLogs as &$log){
                $log['still_need_num'] = intval($log['need_num']) - intval($log['user_num']);
                unset($log['need_num']);
                unset($log['user_num']);
            }
        }
        return $groupLogs;
    }

    //已售数量
    public static function soldNum(Giftpacks $giftpacks){

        //订单数量
        $orderNum = (int)GiftpacksOrder::find()->where([
            "pack_id"    => $giftpacks->id,
            "pay_status" => "paid",
            "is_delete"  => 0
        ])->count();

        //拼单预占数量
        $groupNum = (int)GiftpacksGroup::find()->where([
            "pack_id" => $giftpacks->id,
            "status"  => "sharing"
        ])->andWhere([">", "expired_at", time()])->sum("user_num");

        return ($orderNum + $groupNum);
    }

    //红包抵扣价
    public static function integralDeductionPrice(Giftpacks $giftpacks, User $user){
        return (float)$giftpacks->price;
    }

    //拼单红包抵扣价
    public static function groupIntegralDeductionPrice(Giftpacks $giftpacks, User $user){
        return (float)$giftpacks->group_price;
    }

    //获取大礼包商品数量
    public static function getItemCount(Giftpacks $giftpacks){
        $query = static::availableItemsQuery($giftpacks);
        return (int)$query->count();
    }

    //有效的大礼包物品查询对象a
    public static function availableItemsQuery(Giftpacks $giftpacks){

        $unionQuery1 = GiftpacksOrderItem::find()->alias("goi")
                    ->innerJoin(["go" => GiftpacksOrder::tableName()], "go.id = goi.order_id")
                    ->select([
                        "`goi`.`pack_item_id` as pack_item_id",
                        "ifnull(count(goi.pack_item_id), 0) AS `item_num`"
                    ])->andWhere([
                        "AND",
                        ["`go`.`pay_status`" => "paid"],
                        ["`go`.`pack_id`" => $giftpacks->id],
                        ["`go`.`is_delete`" => 0]
                    ])->groupBy("`goi`.`pack_item_id`");

        $unionQuery2 = GiftpacksGroupPackItem::find()->alias("ggpi")
                    ->innerJoin(["gg" => GiftpacksGroup::tableName()], "gg.id=ggpi.group_id")
                    ->select([
                        "ggpi.pack_item_id as pack_item_id",
                        "ifnull(count(ggpi.pack_item_id), 0) as item_num"
                    ])->andWhere([
                        "AND",
                        ["gg.status" => "sharing"],
                        "gg.expired_at > '".time()."'",
                        ["gg.pack_id" => $giftpacks->id],
                    ])->groupBy("ggpi.pack_item_id");

        $statQuery = BaseActiveRecord::find()->from($unionQuery1->union($unionQuery2))
                                        ->select([
                                            "pack_item_id",
                                            "ifnull(sum(item_num), 0) as order_num"
                                        ])->groupBy("pack_item_id");

        $query = GiftpacksItem::find()->alias("gpi");
        $query->leftJoin(["st" => $statQuery], "st.pack_item_id=gpi.id");
        $query->where(["gpi.is_delete" => 0, "gpi.pack_id" => $giftpacks->id]);
        $query->andWhere([
            "OR",
            "st.order_num IS NULL",
            "st.order_num IS NOT NULL AND gpi.max_stock > st.order_num"
        ]);
        $query->innerJoin(["s" => Store::tableName()], "s.id=gpi.store_id");
        $query->innerJoin(["g" => Goods::tableName()], "g.id=gpi.goods_id");
        $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");

        return $query;
    }

}