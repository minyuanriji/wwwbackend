<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\models\Setting;

class SettingDetailForm extends BaseModel{

    public function getDetail(){

        try {

            $setting = [];
            $rows = Setting::find()->where(["is_delete" => 0])->asArray()->all();
            if($rows){
                foreach($rows as $row){
                    $setting[$row['key']] = $row['value'];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "setting" => $setting
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    /**
     * 获取配置
     * @return mixed
     */
    public static function getSetting(){
        $form = new static();
        $res = $form->getDetail();
        if($res['code'] != ApiCode::CODE_SUCCESS){
            throw new $res['msg'];
        }
        return $res['data']['setting'];
    }
}