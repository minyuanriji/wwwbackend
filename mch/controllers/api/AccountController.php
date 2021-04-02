<?php
namespace app\mch\controllers\api;


use app\mch\forms\api\account\InfoForm;
use app\mch\forms\api\account\WithdrawForm;

class AccountController extends MchMApiController {

    /**
     * 获取账户信息
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionInfo(){
        $form = new InfoForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $form->get();
    }

    /**
     * 账户提现
     * @return \yii\web\Response
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionWithdraw(){
        $form = new WithdrawForm();
        $form->attributes = $this->requestData;
        $form->mch_id = $this->mch_id;
        return $form->save();
    }

}