<?php

namespace app\commands\taobao;

use app\models\Goods;
use app\plugins\taobao\models\TaobaoAccount;
use app\plugins\taobao\models\TaobaoGoods;
use lin010\taolijin\Ali;
use lin010\taolijin\ali\taobao\tbk\item\coupon\TbkItemCouponGetResponse;
use yii\base\Action;

class CheckGoodsAction extends Action{

    public static $sleepTime = 1;

    /**
     * 检查淘宝联盟商品优惠券情况：
     *  优惠券已经过期，执行下架操作
     */
    public function run(){
        $this->controller->commandOut("CheckGoodsAction start");
        while(true){
            try {
                $query = TaobaoGoods::find()->alias("tbg")
                    ->innerJoin(["g" => Goods::tableName()], "g.id=tbg.goods_id")
                    ->orderBy("tbg.check_time ASC")
                    ->select(["tbg.id", "tbg.account_id", "tbg.goods_id", "tbg.num_iid", "tbg.coupon_id"])
                    ->limit(20)->asArray();
                $query->where([
                    "g.status"     => 1,
                    "g.is_delete"  => 0,
                    "g.is_recycle" => 0
                ]);

                $rows = $query->all();
                if($rows){
                    $idArr = [];
                    foreach($rows as $row) ($idArr[] = $row['id']);
                    TaobaoGoods::updateAll([
                        "check_time" => time()
                    ], "id IN (" . implode(",", $idArr) . ")");

                    //根据账号进行分组操作
                    $groupItems = [];
                    foreach($rows as $row){
                        if(!isset($groupItems[$row['account_id']])){
                            $groupItems[$row['account_id']] = [];
                        }
                        $groupItems[$row['account_id']][] = $row;
                    }
                    foreach($groupItems as $account_id => $rows){
                        $this->check($account_id, $rows);
                    }
                }else{
                    $this->active(-1); //活跃度减少
                }
            }catch (\Exception $e){
                $this->controller->commandOut($e->getMessage());
            }
            sleep(static::$sleepTime);
        }
    }

    /**
     * 检查淘联盟商品，下架无优惠券或优惠券已过期商品
     * @param $account_id
     * @param $rows
     * @throws \Exception
     */
    private function check($account_id, $rows){
        $account = TaobaoAccount::findOne($account_id);
        $goodsIds = [];
        foreach($rows as $key => $row){
            $goodsIds[] = $row['goods_id'];
            $rows[$key]['check_pass'] = 0;
        }
        if($account){ //应用账号不存在，全部下架
            $ali = new Ali($account->app_key, $account->app_secret);
            foreach($rows as $key => $row){
                //最多查询3次
                $infoArr = null;
                for($i=0; $i < 3; $i++){
                    $res = $ali->item->couponGet([
                        "item_id"     => $row['num_iid'],
                        "activity_id" => $row['coupon_id']
                    ]);
                    if($res instanceof TbkItemCouponGetResponse){
                        $result = $res->getResult();
                        if(!empty($result) && isset($result['coupon_remain_count'])){
                            $endTime   = strtotime($result['coupon_end_time']) - 3600 * 12;
                            $startTime = strtotime($result['coupon_start_time']);
                            if($result['coupon_remain_count'] > 0 && time() >= $startTime && time() <= $endTime){
                                $rows[$key]['check_pass'] = 1;
                            }else{
                                $this->controller->commandOut("淘宝联盟商品[ID：".$row['goods_id']."]优惠券已过期");
                            }
                            break;
                        }
                    }
                    sleep(1);
                }
            }
        }

        $notPassArr = [];
        foreach($rows as $row){
            if(!$row['check_pass']){
                $notPassArr[] = $row['goods_id'];
            }
        }
        if($notPassArr){
            $this->active(5); //活跃度增加
            Goods::updateAll([
                "status" => 0
            ], "id IN(".implode(",", $notPassArr).")");
        }else{
            $this->active(-1); //活跃度减少
        }
    }

    /**
     * 设置活跃度
     * @param $val
     */
    private function active($val){
        static::$sleepTime = min(max(static::$sleepTime - $val, 1), 20);
    }
}