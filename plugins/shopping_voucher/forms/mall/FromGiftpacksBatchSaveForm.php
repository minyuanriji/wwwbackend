<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;

class FromGiftpacksBatchSaveForm extends BaseModel {

    public $list;
    public $is_all;
    public $do_page;
    public $do_search;
    public $give_type;
    public $give_value;
    public $start_at;
    public $recommender;

    public function rules(){
        return [
            [['is_all', 'give_type', 'give_value', 'start_at'], 'required'],
            [['do_page'], 'integer'],
            [['list', 'do_search', 'recommender'], 'safe'],
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $this->recommender = is_array($this->recommender) ? $this->recommender : [];

            if($this->is_all){
                $form = new FromGiftpacksSearchGiftpacksForm();
                $form->attributes = $this->do_search;
                $form->page = $this->do_page;
                $res = $form->getList();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
                $list = $res['data']['list'];
            }else{
                $list = $this->list;
            }

            if($list){
                foreach($list as $item){
                    $model = ShoppingVoucherFromGiftpacks::findOne([
                        "mall_id" => $item['mall_id'],
                        "pack_id" => $item['pack_id']
                    ]);
                    if(!$model){
                        $model = new ShoppingVoucherFromGiftpacks([
                            "mall_id"    => $item['mall_id'],
                            "pack_id"    => $item['pack_id'],
                            "created_at" => time()
                        ]);
                    }
                    $model->give_type   = $this->give_type;
                    $model->give_value  = max(0, min(100, $this->give_value));
                    $model->updated_at  = time();
                    $model->is_delete   = 0;
                    $model->start_at    = max(time(), strtotime($this->start_at));
                    $model->recommender = @json_encode($this->recommender);
                    if(!$model->save()){
                        throw new \Exception($this->responseErrorMsg($model));
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [],
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }

    }
}