<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderForm;
use app\plugins\alibaba\models\AlibabaDistributionGoodsCategory;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\AlibabaShoppingVoucherGoods;

class ShoppingVoucherGoodsChoosedForm extends BaseModel{

    public $mall_id;
    public $limit = 10;
    public $page;

    public $token;
    public $ss_store_id;
    public $cats;

    public function rules(){
        return [
            [['ss_store_id'], 'required'],
            [['page', 'limit', 'ss_store_id', 'mall_id'], 'integer'],
            [['cats', 'token'], 'safe']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            /*$smartShop = new SmartShop();
            if(!$smartShop->validateToken($this->token)){
                throw new \Exception("无权限操作");
            }*/

            $query = AlibabaShoppingVoucherGoods::find()->alias("asvg")
                ->innerJoin(["g" => AlibabaDistributionGoodsList::tableName()], "g.id=asvg.alibaba_goods_id")
                ->leftJoin(["s" => ShoppingVoucherTargetAlibabaDistributionGoods::tableName()], "s.goods_id=g.id AND s.sku_id=0");

            $query->where(["g.is_delete" => 0, "asvg.ss_store_id" => $this->ss_store_id, "asvg.is_delete" => 0]);

            if($this->cats){
                $orStrs = [];
                foreach($this->cats as $cat){
                    $orStrs[] = "FIND_IN_SET({$cat}, g.ali_category_id)";
                }
                $query->andWhere(implode(" OR ", $orStrs));
            }

            $selects = ["g.id", "g.name", "g.ali_category_id", "g.cover_url", "g.price", "g.origin_price", "g.freight_price", "s.voucher_price"];

            $list = $query->orderBy("asvg.id DESC")->asArray()->select($selects)->page($pagination, $this->limit, $this->page)->all();
            $aliCatIds = [];
            if($list){
                foreach($list as $key => $item){
                    $item['price']           = $item['price'] + (float)$item['freight_price'] * (1/AlibabaDistributionOrderForm::getShoppingVoucherDecodeExpressRate());
                    $item['price']           = round($item['price'], 2);
                    $item['ali_category_id'] = $item['ali_category_id'] ? explode(",", $item['ali_category_id']) : [];
                    $aliCatIds = array_merge($aliCatIds, $item['ali_category_id']);
                    $list[$key] = $item;
                }
                $categorys = $this->getCategorys($aliCatIds);
                foreach($list as $key => $item){
                    $item['cats'] = [];
                    foreach($item['ali_category_id'] as $catId){
                        if(isset($categorys[$catId])){
                            $item['cats'][] = $categorys[$catId];
                        }
                    }
                    $item['cats'] = implode(" / ", $item['cats']);
                    $list[$key] = $item;
                }
            }

            return [
                'code'  => ApiCode::CODE_SUCCESS,
                'count' => isset($pagination->total_count) ? $pagination->total_count : 0,
                'data'  => $list
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 获取分类
     * @param $ids
     * @return array
     */
    private function getCategorys($ids){
        $categorys = [];
        $rows = AlibabaDistributionGoodsCategory::find()->select(["ali_cat_id", "name"])->andWhere(["IN", "ali_cat_id", $ids])->asArray()->all();
        if($rows){
            foreach($rows as $row){
                $categorys[$row['ali_cat_id']] = $row['name'];
            }
        }
        return $categorys;
    }
}