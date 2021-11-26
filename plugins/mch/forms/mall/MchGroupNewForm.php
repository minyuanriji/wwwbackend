<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGroup;
use app\plugins\mch\models\MchGroupItem;

class MchGroupNewForm extends BaseModel{

    public $mch_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id'], 'required']
        ]);
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $mch = Mch::findOne($this->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户[ID:{$this->mch_id}]不存在");
            }

            $store = Store::findOne(["mch_id" => $mch->id]);
            if(!$store || $store->is_delete){
                throw new \Exception("商户[ID:{$this->mch_id}]数据异常");
            }

            if(MchGroupItem::findOne(["mch_id" => $this->mch_id])){
                throw new \Exception("商户[ID:{$this->mch_id}]已是总店或其它分店");
            }

            $mchGroup = MchGroup::findOne(["mch_id" => $this->mch_id]);
            if($mchGroup){
                if(!$mchGroup->is_delete){
                    throw new \Exception("商户[ID:{$this->mch_id}]已是连锁总店");
                }
                $mchGroup->is_delete = 0;
                $mchGroup->deleted_at = 0;
            }else{
                $mchGroup = new MchGroup([
                    "mall_id"    => $mch->mall_id,
                    "mch_id"     => $mch->id,
                    "store_id"   => $store->id,
                    "created_at" => time()
                ]);
            }

            $mchGroup->updated_at = time();
            if(!$mchGroup->save()){
                throw new \Exception($this->responseErrorMsg($mchGroup));
            }

            $mchGroupItem = MchGroupItem::findOne(["mch_id" => $mch->id]);
            if(!$mchGroupItem){
                $mchGroupItem = new MchGroupItem([
                    "mall_id"    => $mchGroup->mall_id,
                    "group_id"   => $mchGroup->id,
                    "mch_id"     => $mch->id,
                    "store_id"   => $store->id,
                    "created_at" => time()
                ]);
            }
            $mchGroupItem->updated_at = time();
            if(!$mchGroupItem->save()){
                throw new \Exception($this->responseErrorMsg($mchGroupItem));
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
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