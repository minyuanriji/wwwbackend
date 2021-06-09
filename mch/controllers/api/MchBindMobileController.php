<?php
namespace app\mch\controllers\api;


use app\mch\forms\api\mch_bind_mobile\UpdateMobileForm;
use app\mch\forms\api\mch_bind_mobile\VerifyOldMobileForm;

class MchBindMobileController extends MchMApiController{

    /**
     * 验证旧手机号码
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionVerify(){
        $form = new VerifyOldMobileForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = $this->mch_id;
        $this->asJson($form->verify());
    }

    /**
     * 修改手机号码
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionUpdate(){
        $form = new UpdateMobileForm();
        $form->attributes = $this->requestData;
        $form->mch_id     = $this->mch_id;
        $this->asJson($form->save());
    }
}