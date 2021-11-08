<?php

namespace app\plugins\oil\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilSetting;

class OilSettingLoadForm extends BaseModel{

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $rows = OilSetting::find()->asArray()->all();
            $settings = [];
            foreach($rows as $row){
                $settings[$row['name']] = $row['value'];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $settings
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}