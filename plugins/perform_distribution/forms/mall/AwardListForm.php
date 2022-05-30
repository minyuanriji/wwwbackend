<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\perform_distribution\models\AwardOrder;

class AwardListForm extends BaseModel{

    public $keyword;
    public $kw_type;
    public $limit = 10;
    public $page = 1;
    public $status;

    public function rules(){
        return [
            [['keyword', 'kw_type'], 'trim'],
            [['limit', 'page', 'status'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = AwardOrder::find()->alias('ao')
                ->leftJoin(['u' => User::tableName()], 'u.id = ao.user_id')
                ->leftJoin(['od' => OrderDetail::tableName()], 'od.id = ao.order_detail_id')
                ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                ->leftJoin(['g' => Goods::tableName()], 'g.id = od.goods_id')
                ->leftJoin(['w' => GoodsWarehouse::tableName()], 'w.id=g.goods_warehouse_id')
                ->where(['ao.is_delete' => 0, 'ao.mall_id' => \Yii::$app->mall->id]);

            $query->andWhere(['ao.status' => $this->status]);

            if ($this->keyword && $this->kw_type) {
                if($this->kw_type == "nickname"){
                    $query->andWhere(["LIKE", "u.nickname", $this->keyword]);
                }elseif($this->kw_type == "user_id"){
                    $query->andWhere(["ao.user_id" => (int)$this->keyword]);
                }elseif($this->kw_type == "goods_name"){
                    $query->andWhere(["LIKE", "w.name", $this->keyword]);
                }elseif($this->kw_type == "order_no"){
                    $query->andWhere(["LIKE", "o.order_no", $this->keyword]);
                }
            }

            $list = $query->select(['ao.*', 'u.nickname', 'u.avatar_url', 'u.mobile', 'o.order_no', 'od.num', 'w.name as goods_name', 'g.price as goods_price', 'w.cover_pic'])
                ->page($pagination, $this->limit, $this->page)
                ->orderBy("ao.id DESC")->asArray()->all();
            if($list){
                foreach($list as $key => $row){
                    $list[$key]['award_info'] = json_decode($row['award_info'], true);
                }
            }

            foreach ($list as $key => $item) {
                $item['total_income'] = (float)AwardOrder::find()->andWhere([
                    "AND",
                    ["mall_id" => \Yii::$app->mall->id],
                    ["user_id" => $item['user_id']],
                    ["IN", "status", [0, 1]],
                    ["is_delete" => 0]
                ])->sum("price");
                $list[$key] = $item;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'list'       => $list,
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}