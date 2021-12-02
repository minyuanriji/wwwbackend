<?php

namespace app\plugins\shopping_voucher\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\goods\ApiGoods;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;
use app\models\PostageRules;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;

class ShoppingVoucherFromGoodsListForm extends BaseModel implements ICacheForm {

    public $page;
    public $stands_mall_id;

    public function rules(){
        return [
            [['page'], 'required'],
            [['page'], 'integer']
        ];
    }
    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Goods::find()->alias('g');
            $query->innerJoin(["svfg" => ShoppingVoucherFromGoods::tableName()], "svfg.goods_id=g.id AND svfg.is_delete=0");
            $query->innerJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');

            $query->where(['g.is_delete' => 0,'g.is_recycle' => 0, 'g.status' => 1, 'g.mall_id' => ($this->base_mall_id)]);

            $selects = ["g.id", "g.is_level", "g.is_show_sales", "gw.cover_pic", "g.goods_stock", "g.freight_id",
                "g.max_deduct_integral", "g.mch_id", "gw.name", "gw.original_price", "g.price", "g.status",
                "gw.unit", "g.use_attr", "gw.video_url", "g.virtual_sales", "g.use_virtual_sales", "svfg.enable_express"
            ];

            $list = $query->orderBy("g.sort DESC,g.id DESC")
                ->select($selects)
                ->asArray()
                ->groupBy('g.goods_warehouse_id')
                ->page($pagination, 20, $this->page)
                ->all();

            $newList = [];
            $goodsIds = [];
            foreach ($list as $detail) {
                $detail['sales'] = sprintf("已售%s%s", $detail['virtual_sales'], $detail['unit']);
                $detail['got_shopping_voucher_num'] = 0;
                $newList[] = $detail;
                $goodsIds[] = $detail['id'];
            }

            //统计销量
            if($goodsIds){
                $rows = OrderDetail::find()->asArray()->select(["goods_id", "count(*) as num"])->andWhere([
                    "AND",
                    ["is_refund" => 0],
                    ["IN", "goods_id", implode(",", $goodsIds)]
                ])->groupBy("goods_id")->all();
                $salesArr = [];
                if($rows){
                    foreach($rows as $row){
                        $salesArr[$row['goods_id']] = $row['num'];
                    }
                }
                foreach($newList as &$detail){
                    if(isset($salesArr[$detail['id']])){
                        $sale = $salesArr[$detail['id']];
                        $detail['sales'] = sprintf("已售%s%s", $sale + $detail['virtual_sales'], $detail['unit']);
                    }
                }
            }


            $fromGoodsRates = [];
            if($goodsIds){
                $fromGoodsData = ShoppingVoucherFromGoods::find()->andWhere([
                    "AND",
                    ["is_delete" => 0],
                    ["IN", "goods_id", $goodsIds]
                ])->select(["goods_id", "give_value"])->asArray()->all();
                foreach($fromGoodsData as &$fromGoods){
                    $fromGoodsRates[$fromGoods['goods_id']] = $fromGoods['give_value'];
                }
            }
            foreach($newList as &$detail){
                if(isset($fromGoodsRates[$detail['id']])){
                    $rate = $fromGoodsRates[$detail['id']];
                    $detail['got_shopping_voucher_num'] = round((floatval($rate)/100) * floatval($detail['price']), 2);
                    if ($detail['enable_express']) {
                        if ($detail['freight_id']) {
                            $freight = PostageRules::findOne(['id' => $detail['freight_id'], 'is_delete' => 0]);
                        } else {
                            $freight = PostageRules::findOne([
                                'mall_id' => \Yii::$app->mall->id,
                                'status' => 1,
                                'is_delete' => 0,
                            ]);
                        }
                        if ($freight && $freight->detail) {
                            $freightDetail = @json_decode($freight->detail, true);
                            $detail['got_shopping_voucher_num'] += $freightDetail[0]['firstPrice'];
                        }
                    }
                }
            }

            $sourceData = $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '',
                [
                    'list' => $newList,
                    'page_count' => $pagination->page_count,
                    'total_count' => $pagination->total_count
                ]
            );

            return new APICacheDataForm([
                "sourceData" => $sourceData
            ]);

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        $keys = [max(1, intval($this->page))];
        return $keys;
    }
}