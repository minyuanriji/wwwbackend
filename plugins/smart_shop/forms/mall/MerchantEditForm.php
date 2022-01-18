<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;

class MerchantEditForm extends BaseModel{

    public $shop_list;
    public $bsh_mch_id;
    public $start_at;

    public function rules(){
        return [
            [['bsh_mch_id', 'start_at'], 'required'],
            [['bsh_mch_id'], 'integer'],
            [['shop_list'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            if(!$this->shop_list){
                throw new \Exception("请添加要绑定的智慧门店");
            }

            $merchant = Merchant::findOne(["bsh_mch_id" => $this->bsh_mch_id]);
            if(!$merchant){
                $merchant = new Merchant([
                    "mall_id"    => \Yii::$app->mall->id,
                    "bsh_mch_id" => $this->bsh_mch_id,
                    "created_at" => time()
                ]);
            }
            $merchant->updated_at = time();
            $merchant->start_at   = strtotime($this->start_at);
            $merchant->is_delete  = 0;
            if(!$merchant->save()){
                throw new \Exception($this->responseErrorMsg($merchant));
            }

            $smartShop = new SmartShop();

            $oldStoreIds = MerchantFzlist::find()->where([
                "bsh_mch_id" => $merchant->bsh_mch_id,
                "is_delete"  => 0
            ])->select(["ss_store_id"])->column();
            if($oldStoreIds){
                $smartShop->batchSetStoreSplitDisable($oldStoreIds);
            }

            MerchantFzlist::updateAll(["is_delete" => 1], ["bsh_mch_id" => $merchant->bsh_mch_id]);

            $storeIds = [];
            foreach($this->shop_list as $shop){
                $model = MerchantFzlist::findOne([
                    "bsh_mch_id"  => $merchant->bsh_mch_id,
                    "ss_mch_id"   => $shop['ss_mch_id'],
                    "ss_store_id" => $shop['ss_store_id']
                ]);
                if(!$model){
                    $model = new MerchantFzlist([
                        "mall_id"     => $merchant->mall_id,
                        "bsh_mch_id"  => $merchant->bsh_mch_id,
                        "ss_mch_id"   => $shop['ss_mch_id'],
                        "ss_store_id" => $shop['ss_store_id']
                    ]);
                }
                $model->name      = $shop['name'];
                $model->logo      = $shop['logo'];
                $model->mobile    = $shop['mobile'];
                $model->address   = $shop['address'];
                $model->is_delete = 0;
                if(!$model->save()){
                    throw new \Exception($this->responseErrorMsg($model));
                }
                $storeIds[] = $shop['ss_store_id'];
            }


            $smartShop->batchSetStoreSplitEnable($storeIds, $merchant->start_at, [
                'wechat_fz_account' => $smartShop->setting['wechat_fz_account'],
                'wechat_fz_type'    => $smartShop->setting['wechat_fz_type'],
                'ali_fz_account'    => $smartShop->setting['ali_fz_account'],
                'ali_fz_type'       => $smartShop->setting['ali_fz_type'],
            ]);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}