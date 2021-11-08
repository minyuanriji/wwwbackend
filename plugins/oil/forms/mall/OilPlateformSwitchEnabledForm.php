<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;

class OilPlateformSwitchEnabledForm extends BaseModel{

    public $id;
    public $enabled;

    public function rules(){
        return [
            [['id', 'enabled'], 'required']
        ];
    }

    public function update(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $plateform = OilPlateforms::findOne($this->id);
            if(!$plateform){
                throw new \Exception("平台[ID:{$this->id}]不存在");
            }

            if($this->enabled){
                OilPlateforms::updateAll(["is_enabled" => 0]);
            }

            $plateform->is_enabled = (int)$this->enabled;
            $plateform->updated_at = time();
            if(!$plateform->save()){
                throw new \Exception($this->responseErrorMsg($plateform));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}