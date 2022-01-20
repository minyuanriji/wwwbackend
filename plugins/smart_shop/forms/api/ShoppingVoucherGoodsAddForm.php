<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\AlibabaShoppingVoucherGoods;
use app\plugins\smart_shop\models\MerchantFzlist;

class ShoppingVoucherGoodsAddForm extends BaseModel{

    public $token;
    public $ss_store_id;
    public $id_list;

    public function rules(){
        return [
            [['ss_store_id', 'token', 'id_list'], 'required'],
            [['token'], 'trim'],
            [['ss_store_id'], 'integer']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $smartShop = new SmartShop();
            if(!$smartShop->validateToken($this->token)){
                throw new \Exception("无权限操作");
            }

            $fzStore = MerchantFzlist::findOne(["ss_store_id" => $this->ss_store_id]);
            if(!$fzStore || $fzStore->is_delete){
                throw new \Exception("未关联补商汇商家系统");
            }

            $mch = Mch::findOne($fzStore->bsh_mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("MCH商户[ID:{$fzStore->bsh_mch_id}]不存在");
            }

            foreach($this->id_list as $id){
                $model = AlibabaShoppingVoucherGoods::findOne([
                    "alibaba_goods_id" => $id,
                    "ss_store_id"      => $fzStore->ss_store_id
                ]);
                if(!$model){
                    $model = new AlibabaShoppingVoucherGoods([
                        "mall_id"          => $mch->mall_id,
                        "alibaba_goods_id" => $id,
                        "ss_store_id"      => $fzStore->ss_store_id,
                        "created_at"       => time()
                    ]);
                }
                $model->updated_at = time();
                $model->is_delete  = 0;
                if(!$model->save()){
                    throw new \Exception($this->responseErrorMsg($model));
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => []
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}