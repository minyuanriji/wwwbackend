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
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\models\Mch;

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

            $groupList = [];

            //如果支持拼单
            $myGroup = ['has_group' => 0, 'is_owner' => 0, 'group_id' => 0];
            if($giftpacks->group_enable && !\Yii::$app->user->isGuest){
                //获取最新的两条拼单记录
                $groupList = static::newestGroupLog($giftpacks);

                //获取我发起或我参与未结束的团
                $userId = \Yii::$app->user->id;
                $groupData = GiftpacksGroupPayOrder::find()->alias("ggpo")
                    ->innerJoin(["gg" => GiftpacksGroup::tableName()], "gg.id=ggpo.group_id")
                    ->andWhere([
                        "AND",
                        ["gg.pack_id" => $giftpacks->id],
                        ["gg.status"  => "sharing"]
                    ])->andWhere([
                        "OR",
                        ["gg.user_id" => $userId],
                        "ggpo.user_id='{$userId}' AND ggpo.pay_status='paid'"
                    ])->select(["gg.*"])->asArray()->one();
                if($groupData){
                    $myGroup['has_group'] = 1;
                    $myGroup['is_owner']  = $groupData['user_id'] == $userId ? 1 : 0;
                    $myGroup['group_id']  = (int)$groupData['id'];
                }
            }


            //获取最新两条可参与的拼团
            $joinGroups = GiftpacksGroup::find()->alias("gg")
                        ->innerJoin(["u" => User::tableName()], "u.id=gg.user_id")
                        ->andWhere([
                            "AND",
                            ["gg.pack_id" => $giftpacks->id],
                            ["gg.status" => "sharing"],
                            [">", "gg.expired_at", time()],
                            "gg.need_num > gg.user_num"
                        ])->select(["gg.id as group_id", "u.nickname", "u.avatar_url", "gg.need_num", "gg.user_num", "gg.expired_at"])
                        ->asArray()->orderBy("gg.id DESC")->limit(50)->all();
            if($joinGroups){
                foreach($joinGroups as &$joinGroup){
                    $joinGroup['need_num'] = $joinGroup['need_num'] - $joinGroup['user_num'];
                }
            }

            $detail['sold_num'] += 300;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail'      => $detail,
                    'my_group'    => $myGroup,
                    'group_list'  => $groupList ? $groupList : [],
                    'join_groups' => $joinGroups ? $joinGroups : []
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

        $detail['id']                  = $giftpacks->id;
        $detail['title']               = $giftpacks->title;
        $detail['cover_pic']           = $giftpacks->cover_pic;
        $detail['descript']            = $giftpacks->descript;
        $detail['max_stock']           = $giftpacks->max_stock;
        $detail['group_enable']        = $giftpacks->group_enable;
        $detail['group_price']         = $giftpacks->group_price;
        $detail['group_num']           = $giftpacks->group_need_num;
        $detail['group_hour_expired']  = intval($giftpacks->group_expire_time / 3600);
        $detail['price']               = $giftpacks->price;
        $detail['purchase_limits_num'] = $giftpacks->purchase_limits_num;
        $detail['allow_currency']      = $giftpacks->allow_currency;
        $detail['integral_enable']     = $giftpacks->integral_enable;
        $detail['integral_give_num']   = $giftpacks->integral_give_num;

        $detail['is_finished']         = time() > $giftpacks->expired_at ? 1 : 0;
        $detail['expired_at']          = $giftpacks->expired_at;
        $detail['view_num']            = static::viewNum($giftpacks);

        $detail['item_count']          = static::getItemCount($giftpacks);
        $detail['sold_num']            = static::soldNum($giftpacks);

        $detail['pic_url']             = !empty($giftpacks->pic_url) ? json_decode($giftpacks->pic_url, true) : [];
        $detail['detail']              = $giftpacks->detail;


        return $detail;
    }

    /**
     * 获取浏览次数
     * @param Giftpacks $giftpacks
     * @return int
     */
    public static function viewNum(Giftpacks $giftpacks){

        $minNum = 2513;

        $giftpacks->view_num = max($minNum, $giftpacks->view_num);

        $cache = \Yii::$app->getCache();
        $cacheKey = "GiftpacksViewNum:" . $giftpacks->id;
        $addNum = (int)$cache->get($cacheKey);

        if($addNum > 10){
            $giftpacks->view_num += $addNum;
            $giftpacks->save();
            $addNum = 0;
        }else{
            $addNum++;
        }
        $cache->set($cacheKey, $addNum);

        $viewNum = max($minNum, $giftpacks->view_num);

        return ($viewNum + $addNum);
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

    //有效的大礼包商品查询对象
    public static function availableItemsQuery(Giftpacks $giftpacks){
        return static::availableItemsQueryByPackId($giftpacks->id);
    }

    //通过大礼包ID获取有效的大礼包商品查询对象
    public static function availableItemsQueryByPackId($pack_id){

        $unionQuery1 = GiftpacksOrderItem::find()->alias("goi")
                    ->innerJoin(["go" => GiftpacksOrder::tableName()], "go.id = goi.order_id")
                    ->select([
                        "`goi`.`pack_item_id` as pack_item_id",
                        "ifnull(count(goi.pack_item_id), 0) AS `item_num`"
                    ])->andWhere([
                        "AND",
                        ["`go`.`pay_status`" => "paid"],
                        ["`go`.`pack_id`" => $pack_id],
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
                        ["gg.pack_id" => $pack_id],
                    ])->groupBy("ggpi.pack_item_id");

        $statQuery = BaseActiveRecord::find()->from($unionQuery1->union($unionQuery2))
                                        ->select([
                                            "pack_item_id",
                                            "ifnull(sum(item_num), 0) as order_num"
                                        ])->groupBy("pack_item_id");

        $query = GiftpacksItem::find()->alias("gpi");
        $query->leftJoin(["st" => $statQuery], "st.pack_item_id=gpi.id");
        $query->where(["gpi.is_delete" => 0, "gpi.pack_id" => $pack_id]);
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