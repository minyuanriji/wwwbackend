<?php

namespace app\plugins\shopping_voucher\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\goods\ApiGoods;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;

class ShoppingVoucherFromGoodsListForm extends BaseModel implements ICacheForm {

    public $page;

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

            $query = Goods::find()->alias('g')->with(['goodsWarehouse', 'attr']);
            $query->innerJoin(["svfg" => ShoppingVoucherFromGoods::tableName()], "svfg.goods_id=g.id AND svfg.is_delete=0");
            $query->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');
            $query->where(['g.is_delete' => 0,'g.is_recycle' => 0, 'g.status' => 1, 'g.mall_id' => $this->base_mall_id]);

            $list = $query->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
                ->groupBy('g.goods_warehouse_id')
                ->page($pagination, 10, $this->page)
                -> all();

            $newList = [];
            /* @var Goods[] $list */
            $goodsIds = [];
            foreach ($list as $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 1;
                $detail = $apiGoods->getDetail();
                $detail['app_share_title'] = $item->app_share_title;
                $detail['app_share_pic'] = $item->app_share_pic;
                $detail['use_attr'] = $item->use_attr;
                $detail['unit'] = $item->unit;
                if ($item->use_virtual_sales) {
                    $detail['sales'] = sprintf("已售%s%s", $item->sales + $item->virtual_sales, $item->unit);
                }
                $detail['got_shopping_voucher_num'] = 0;
                $newList[] = $detail;
                $goodsIds[] = $detail['id'];
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