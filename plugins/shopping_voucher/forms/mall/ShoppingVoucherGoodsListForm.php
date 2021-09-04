<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetGoods;

class ShoppingVoucherGoodsListForm extends BaseModel{

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

            $query = ShoppingVoucherTargetGoods::find()->where(["is_delete" => 0]);
            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["goods_id" => (int)$this->keyword],
                    ["LIKE", "name", $this->keyword]
                ]);
            }

            $query->orderBy("id DESC");

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