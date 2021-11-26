<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGroup;
use app\plugins\mch\models\MchGroupItem;

class MchGroupAddItemForm extends BaseModel{

    public $group_id;
    public $mch_id;

    public function rules(){
        return [
            [['group_id', 'mch_id'], 'required']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $mchGroup = MchGroup::findOne($this->group_id);
            if(!$mchGroup || $mchGroup->is_delete){
                throw new \Exception("总店[ID:{$this->group_id}]信息不存在");
            }

            $mch = Mch::findOne($this->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户[ID:{$this->mch_id}]不存在");
            }

            $store = Store::findOne(["mch_id" => $mch->id]);
            if(!$store || $store->is_delete){
                throw new \Exception("商户[ID:{$this->mch_id}]数据异常");
            }

            $mchGroupItem = MchGroupItem::findOne(["mch_id" => $mch->id]);
            if($mchGroupItem){
                throw new \Exception("商户[ID:{$this->mch_id}]已是子店铺，请勿重复设置");
            }

            $mchGroupItem = new MchGroupItem([
                "mall_id"    => $mchGroup->mall_id,
                "group_id"   => $mchGroup->id,
                "mch_id"     => $mch->id,
                "store_id"   => $store->id,
                "created_at" => time(),
                "updated_at" => time()
            ]);
            if(!$mchGroupItem->save()){
                throw new \Exception($this->responseErrorMsg($mchGroupItem));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}