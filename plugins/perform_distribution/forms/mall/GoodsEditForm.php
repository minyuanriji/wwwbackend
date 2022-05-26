<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\plugins\commission\forms\mall\CommissionRuleEditForm;
use app\plugins\perform_distribution\models\PerformDistributionGoods;

class GoodsEditForm extends BaseModel{

    public $id;
    public $goods_id;
    public $award_type;
    public $award_rules;

    public function rules(){
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'id', 'award_type'], 'integer'],
            [['award_rules'], 'trim']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            $goods = Goods::findOne($this->goods_id);
            if(!$goods || $goods->is_delete || $goods->is_recycle || $goods->status != Goods::STATUS_ON){
                throw new \Exception("商品不存在或已下架");
            }

            $performDistributionGoods = PerformDistributionGoods::findOne(["goods_id" => $this->goods_id]);
            if(!$performDistributionGoods){
                $performDistributionGoods = new PerformDistributionGoods([
                    "mall_id"    => $goods->mall_id,
                    "goods_id"   => $goods->id,
                    "created_at" => time()
                ]);
            }
            $performDistributionGoods->award_type = $this->award_type;
            $performDistributionGoods->award_rules = $this->award_rules;
            $performDistributionGoods->updated_at = time();
            $performDistributionGoods->is_delete  = 0;
            if(!$performDistributionGoods->save()){
                throw new \Exception($this->responseErrorMsg($performDistributionGoods));
            }

            //自动取消该商品的分佣规则
            $form = new CommissionRuleEditForm();
            $form->item_type              = "goods";
            $form->apply_all_item         = 0;
            $form->item_id                = $this->goods_id;
            $form->commission_type        = 1;
            $form->commission_chains_json = '[{"role_type":"branch_office","level":1,"commisson_value":"0","unique_key":"branch_office#all"},{"role_type":"branch_office","level":2,"commisson_value":0,"unique_key":"branch_office#partner#all"},{"role_type":"branch_office","level":2,"commisson_value":0,"unique_key":"branch_office#store#all"},{"role_type":"branch_office","level":3,"commisson_value":0,"unique_key":"branch_office#partner#store#all"},{"role_type":"branch_office","level":3,"commisson_value":0,"unique_key":"branch_office#partner#partner#all"},{"role_type":"branch_office","level":4,"commisson_value":0,"unique_key":"branch_office#partner#partner#store#all"},{"role_type":"partner","level":1,"commisson_value":0,"unique_key":"partner#all"},{"role_type":"partner","level":2,"commisson_value":0,"unique_key":"partner#partner#all"},{"role_type":"partner","level":2,"commisson_value":0,"unique_key":"partner#store#all"},{"role_type":"partner","level":3,"commisson_value":0,"unique_key":"partner#partner#store#all"},{"role_type":"store","level":1,"commisson_value":0,"unique_key":"store#all"}]';
            $res = $form->save();
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            $t->commit();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null);
        }catch (\Exception $e){
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}