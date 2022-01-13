<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\models\Setting;

class SettingSaveForm extends BaseModel{

    public $wechat;
    public $form;

    public function rules(){
        return [
            [['form'], 'required'],
            [['wechat'], 'integer']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {
            foreach($this->form as $key => $value){
                $key = trim($key);
                if(empty($key)) continue;
                $setting = Setting::findOne(["key" => $key]);
                if(!$setting){
                    $setting = new Setting([
                        "mall_id" => \Yii::$app->mall->id,
                        "key"     => $key
                    ]);
                }
                $setting->value     = trim($value);
                $setting->is_delete = 0;
                if(!$setting->save()){
                    throw new \Exception($this->responseErrorMsg($setting));
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