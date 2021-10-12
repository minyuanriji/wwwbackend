<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;

class AlibabaDistributionSearchGoodsForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $user_id;
    public $page;
    public $ali_cat_id;
    public $recommend;

    public function rules(){
        return [
            [['page'], 'required'],
            [['mall_id', 'user_id', 'page', 'ali_cat_id', 'recommend'], 'integer']
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
            $query = AlibabaDistributionGoodsList::find()->alias("g")->where(["g.is_delete" => 0]);
            $query->leftJoin(["s" => ShoppingVoucherTargetAlibabaDistributionGoods::tableName()], "s.goods_id=g.id AND s.sku_id=0");

            if($this->ali_cat_id){
                $query->andWhere("FIND_IN_SET('{$this->ali_cat_id}', g.ali_category_id)");
            }

            if ($this->recommend) {
                $query->andWhere(['g.is_recommend' => $this->recommend]);
            }

            $orderBy = "g.id DESC";
            $query->orderBy($orderBy);

            $selects = ["g.id", "g.name", "g.cover_url", "g.price", "g.origin_price", "g.freight_price", "s.voucher_price"];
            $list = $query->asArray()->select($selects)->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $item['price'] = $item['price'] + (float)$item['freight_price'] * (1/AlibabaDistributionOrderForm::getShoppingVoucherDecodeExpressRate());
                    $item['price'] = round($item['price'], 2);
                }
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'list' => $list ? $list : [],
                        'pagination' => $pagination
                    ]
                ]
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
        $keys = [$this->mall_id, $this->user_id, $this->page, $this->ali_cat_id];
        return $keys;
    }
}