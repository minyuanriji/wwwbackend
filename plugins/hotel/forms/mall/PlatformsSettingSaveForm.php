<?php

namespace app\plugins\hotel\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Option;

class PlatformsSettingSaveForm extends BaseModel{

    public $jsonSettingArray;

    public function rules(){
        return [
            [['jsonSettingArray'], 'required']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $option = Option::findOne([
                "mall_id" => \Yii::$app->mall->id,
                "group"   => "hotel",
                "name"    => "platforms_setting"
            ]);
            if(!$option){
                $option = new Option([
                    "mall_id"    => \Yii::$app->mall->id,
                    "group"      => "hotel",
                    "name"       => "platforms_setting",
                    "created_at" => time()
                ]);
            }

            $option->updated_at = time();
            $option->value      = $this->jsonSettingArray;
            if(!$option->save()){
                throw new \Exception($this->responseErrorMsg($option));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}