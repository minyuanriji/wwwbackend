<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;

class FromGoodsListForm extends BaseModel {

    public $page;
    public $limit;

    public function rules(){
        return [
            [['page', 'limit'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $commonData = ["is_open" => 0, "give_value"  => "", "start_at" => ""];
            $fromGoods = ShoppingVoucherFromGoods::findOne(["goods_id" => 0, "mall_id" => \Yii::$app->mall->id]);
            if($fromGoods){
                $commonData["is_open"]    = !$fromGoods->is_delete ? 1 : 0;
                $commonData["give_value"] = $fromGoods->give_value;
                $commonData["start_at"]   = date("Y-m-d", $fromGoods->start_at);
            }

            $pagination = null;

            $query = ShoppingVoucherFromGoods::find()->alias("svfg")->where(["svfg.is_delete" => 0, "svfg.mall_id" => \Yii::$app->mall->id]);
            $query->innerJoin(["g" => Goods::tableName()], "g.id=svfg.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");

            $selects = ["svfg.*", "gw.name", "g.id as goods_id", "gw.cover_pic"];
            $query->orderBy("svfg.id DESC");

            $list = $query->select($selects)->page($pagination, 10, $this->page)->asArray()->all();
            if($list) {
                foreach ($list as &$item) {
                    $item['start_at'] = date("Y-m-d", $item['start_at'] ? $item['start_at'] : time());
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'commonData' => $commonData,
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}