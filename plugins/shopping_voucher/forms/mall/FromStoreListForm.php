<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

class FromStoreListForm extends BaseModel {

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

            $query = ShoppingVoucherFromStore::find()->where(["is_delete" => 0]);
            if($this->keyword){
                $query->andWhere([
                    "OR",
                    ["mch_id" => (int)$this->keyword],
                    ["LIKE", "name", $this->keyword]
                ]);
            }

            $query->orderBy("id DESC");

            $list = $query->page($pagination, $this->limit, $this->page)->asArray()->all();

            if($list){
                foreach($list as &$item){
                    $item['start_at'] = date("Y-m-d", $item['start_at'] ? $item['start_at'] : time());
                }
            }

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