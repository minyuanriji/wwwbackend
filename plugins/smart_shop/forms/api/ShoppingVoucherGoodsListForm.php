<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderForm;
use app\plugins\alibaba\forms\api\AlibabaDistributionSearchGoodsForm;

class ShoppingVoucherGoodsListForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $ali_cat_id;
    public $ss_store_id; //智慧经营门店ID
    public $user_mobile; //授权用户手机号码
    public $limit = 10;
    public $page;
    public $keyword;

    public function rules(){
        return [
            [['page', 'limit', 'ss_store_id', 'mall_id', 'ali_cat_id'], 'integer'],
            [['user_mobile', 'keyword'], 'trim']
        ];
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        $keys = [$this->mall_id, $this->ss_store_id, $this->user_mobile, $this->limit, $this->page, $this->ali_cat_id];
        return $keys;
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $user = null;
            if($this->user_mobile){
                $user = User::findOne(["mobile" => $this->user_mobile]);
            }

            $form = new AlibabaDistributionSearchGoodsForm();
            $form->ali_cat_id = $this->ali_cat_id;
            $form->mall_id    = $this->mall_id;
            $form->user_id    = $user ? $user->id : 0;

            $query = $form->getQuery();
            $query->andWhere([
                "AND",
                "s.voucher_price IS NOT NULL"
            ]);

            if(!empty($this->keyword)){
                $query->andWhere(["LIKE", "g.name", $this->keyword]);
            }

            $selects = ["g.id", "g.name", "g.cover_url", "g.price", "g.origin_price", "g.freight_price", "s.voucher_price"];
            $list = $query->asArray()->orderBy("g.id DESC")->select($selects)->page($pagination, $this->limit, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $price = $item['price'] + (float)$item['freight_price'] * (1/AlibabaDistributionOrderForm::getShoppingVoucherDecodeExpressRate());
                    $item['price'] = round($price, 2);
                }
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code'  => ApiCode::CODE_SUCCESS,
                    'count' => isset($pagination->total_count) ? $pagination->total_count : 0,
                    'data'  => $list
                ]
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}