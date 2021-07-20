<?php

namespace app\plugins\addcredit\controllers\mall\account;

use app\plugins\addcredit\forms\mall\AccountForm;
use app\plugins\Controller;

class AccountController extends Controller
{
    /**
     * 账户余额查询
     * @return string|\yii\web\Response
     */
    public function actionAccountBalance ()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AccountForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->balanceQuery());
        } else {
            return $this->render('account-balance');
        }
    }
}