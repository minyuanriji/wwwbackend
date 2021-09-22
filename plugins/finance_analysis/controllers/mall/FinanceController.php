<?php

namespace app\plugins\finance_analysis\controllers\mall;

use app\plugins\Controller;
use app\plugins\finance_analysis\forms\mall\FinanceDetailsForm;
use app\plugins\finance_analysis\forms\mall\FinanceIncomeStatForm;

class FinanceController extends Controller
{

    public function actionAnalysis()
    {
        return $this->render('analysis');
    }

    /**
     * 收支统计
     * @return \yii\web\Response
     */
    public function actionIncomeStat()
    {
        $form = new FinanceIncomeStatForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->get());
    }

    /**
     * 收支明细(个人)
     * @return
     */
    public function actionFinanceDetails()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new FinanceDetailsForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->get());
        } else {
            return $this->render('finance-details');
        }
    }
}