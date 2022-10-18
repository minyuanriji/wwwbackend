<?php

namespace app\plugins\income_log\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\commission\models\CommissionGoodsPriceLog;
use app\plugins\income_log\models\Setting;

class IncomeDataForm extends BaseModel{

    public $status;
    public $page;
    public $start_date;
    public $end_date;
    public $keywords;

    public function rules(){
        return [
            [['status', 'page'] , 'integer'],
            [['start_date', 'end_date', 'keywords'], 'trim']
        ];
    }

    public function getData(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $settings = Setting::getSettings();
            $incomeShowGoods = isset($settings['income_show_goods']) && !empty($settings['income_show_goods']) ?
                json_decode($settings['income_show_goods'], true) : [];
            $showGoodsIds = [];
            foreach($incomeShowGoods as $item){
                $showGoodsIds[] = $item['id'];
            }

            $query = CommissionGoodsPriceLog::find()->alias("pl")
                ->leftJoin(["g" => Goods::tableName()], "g.id=pl.goods_id")
                ->leftJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id")
                ->leftJoin(["od" => OrderDetail::tableName()], "od.id=pl.order_detail_id")
                ->leftJoin(["o" => Order::tableName()], "o.id=pl.order_id")
                ->leftJoin(["u" => User::tableName()], "u.id=o.user_id")
                ->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");

            $query->andWhere([
                "AND",
                ["pl.user_id" => \Yii::$app->user->id],
                ["pl.status" => $this->status],
                ["IN", "pl.goods_id", $showGoodsIds]
            ]);

            if($this->start_date && $this->end_date){
                $startTime = strtotime($this->start_date . " 00:00:00");
                $endTime = strtotime($this->end_date . " 23:59:59");
                $query->andWhere([">", "pl.created_at", $startTime]);
                $query->andWhere(["<", "pl.created_at", $endTime]);
            }

            if($this->keywords){
                $query->andWhere([
                    "OR",
                    ["LIKE", "gw.name", $this->keywords],
                    ["LIKE", "o.order_no", $this->keywords],
                    ["LIKE", "u.nickname", $this->keywords],
                    ["LIKE", "u.mobile", $this->keywords],
                    ["u.id" => $this->keywords],
                    ["g.id" => $this->keywords],
                ]);
            }

            $query->select([
                "pl.price as income_price",
                "o.order_no", "o.created_at", "o.total_price", "od.num", "gw.name as goods_name", "gw.cover_pic", "g.price",
                "u.nickname", "u.avatar_url", "u.id as user_id", "u.mobile", "g.id AS goods_id",
                "p.nickname as parent_nickname", "od.goods_info"
            ]);

            $query->orderBy("pl.id DESC");
            $list = $query->page($pagination, 10, max(1, (int)$this->page))->asArray()->all();
            if($list){
                foreach($list as $key => $row){
                    $goodsInfo = !empty($row['goods_info']) ? json_decode($row['goods_info'], true) : [];
                    $list[$key]['attr_name'] = !empty($goodsInfo['attr_list']) ? $goodsInfo['attr_list'][0]['attr_name'] : $row['goods_name'];
                    $list[$key]['mobile'] = !empty($row['mobile']) ? substr($row['mobile'], 0, 3) . "***" . substr($row['mobile'], -4) : "";
                    $list[$key]['created_at'] = date("Y-m-d H:i:s", $row['created_at']);
                }
            }

            //预计到账总收益
            $statPrice1 = CommissionGoodsPriceLog::find()->andWhere([
                "AND",
                ["user_id" => \Yii::$app->user->id],
                ["status" => 0],
                ["IN", "goods_id", $showGoodsIds]
            ])->sum("price");

            //已到账总收益
            $statPrice2 = CommissionGoodsPriceLog::find()->andWhere([
                "AND",
                ["user_id" => \Yii::$app->user->id],
                ["status" => 1],
                ["IN", "goods_id", $showGoodsIds]
            ])->sum("price");


            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'list'        => $list ? $list : [],
                'pagination'  => $pagination,
                "stat_price1" => round($statPrice1, 2),
                "stat_price2" => round($statPrice2, 2),
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL);
        }

    }

}