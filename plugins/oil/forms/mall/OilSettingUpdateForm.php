<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilSetting;

class OilSettingUpdateForm extends BaseModel{

    public $settings;

    public function rules(){
        return [
            [['settings'], 'required']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $settings = is_array($this->settings) ? $this->settings : [];
            foreach($settings as $name => $value){
                $name = trim($name);
                if(empty($name)) continue;
                $oilSetting = OilSetting::findOne(["name" => $name]);
                if(!$oilSetting){
                    $oilSetting = new OilSetting([
                        "mall_id"    => \Yii::$app->mall->id,
                        "name"       => $name,
                        "created_at" => time()
                    ]);
                }
                $oilSetting->value      = trim($value);
                $oilSetting->updated_at = time();
                if(!$oilSetting->save()){
                    throw new \Exception($this->responseErrorMsg($oilSetting));
                }
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