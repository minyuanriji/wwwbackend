<?php

namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\forms\mall\AlibabaDistributionAbnormalTransactionForm;
use app\plugins\Controller;

class AbnormalTransactionController extends Controller
{
    /**
     * 交易异常列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AlibabaDistributionAbnormalTransactionForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }
}