<?php

namespace app\mch\controllers\api;

use app\plugins\mch\forms\common\apply\MchApplyInfoForm;

class ApplyController extends MchMApiController{

    /**
     * 获取申请信息
     * @return \yii\web\Response
     */
    public function actionInfo(){
        $form = new MchApplyInfoForm();
        $form->attributes = $this->requestData;
        $form->user_id = \Yii::$app->user->id;
        return $this->asJson($form->get());
    }
}