<?php

namespace app\plugins\income_log\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\income_log\models\Setting;

class SettingSaveForm extends BaseModel{

    public $form;

    public function rules(){
        return [
            [['form'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {
            foreach($this->form as $name => $value){
                $setting = Setting::findOne([
                    "mall_id" => \Yii::$app->mall->id,
                    "name"    => $name,
                ]);
                if(!$setting){
                    $setting = new Setting([
                        "mall_id"    => \Yii::$app->mall->id,
                        "name"       => $name,
                        "created_at" => time()
                    ]);
                }
                $setting->updated_at = time();
                $setting->value      = $value;
                if(!$setting->save()){
                    throw new \Exception($this->responseErrorMsg($setting));
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL);
        }
    }
}