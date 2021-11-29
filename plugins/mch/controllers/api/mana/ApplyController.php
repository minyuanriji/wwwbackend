<?php
namespace app\plugins\mch\controllers\api\mana;

use app\plugins\mch\forms\common\apply\MchApplyInfoForm;

class ApplyController extends MchAdminController
{
    /**
     * 获取申请信息
     * @return \yii\web\Response
     */
    public function actionInfo(){
        $form = new MchApplyInfoForm();
        $form->attributes = $this->requestData;
        $form->user_id = MchAdminController::$adminUser['mch']['user_id'];
        return $this->asJson($form->get());
    }
}