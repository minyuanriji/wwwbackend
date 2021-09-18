<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;

class ShoppingVoucherAlibabaDistributionGoodsListForm extends BaseModel{

    public $page;
    public $limit;
    public $keyword;

    public function rules(){
        return [
            [['page', 'limit'], 'integer'],
            [['keyword'], 'trim'],
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = ShoppingVoucherTargetAlibabaDistributionGoods::find()->alias("tg")->where(["tg.is_delete" => 0]);

            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["tg.goods_id" => (int)$this->keyword],
                    ["LIKE", "tg.name", $this->keyword]
                ]);
            }

            $query->orderBy("tg.id DESC");

            $list = $query->page($pagination, $this->limit, $this->page)->asArray()->all();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
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