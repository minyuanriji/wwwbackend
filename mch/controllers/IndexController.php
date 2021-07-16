<?php
namespace app\mch\controllers;

use app\core\ApiCode;
use app\plugins\mch\forms\mall\MchMallSettingForm;

class IndexController extends MchController {


    public function actionMallPermissions(){

        $permissions = [];
        $form = new MchMallSettingForm();
        $form->mch_id = \Yii::$app->mchAdmin->identity->mchModel->id;
        $form->mall_id = \Yii::$app->mall->id;
        $setting = $form->search();
        if ($setting && $setting->is_distribution) {
            $permissions[] = 'area';
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'permissions' => $permissions
            ]
        ];
    }


}