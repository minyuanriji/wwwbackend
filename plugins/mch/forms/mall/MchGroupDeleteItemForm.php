<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchGroup;
use app\plugins\mch\models\MchGroupItem;

class MchGroupDeleteItemForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function delete(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $mchGroupItem = MchGroupItem::findOne($this->id);
            if(!$mchGroupItem){
                throw new \Exception("记录[ID:{$this->id}]不存在");
            }

            $mchGroup = MchGroup::findOne($mchGroupItem->group_id);
            if($mchGroup && $mchGroup->mch_id == $mchGroupItem->mch_id){
                throw new \Exception("总店[ID:{$mchGroupItem->mch_id}]不能删除");
            }

            $mchGroupItem->delete();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}