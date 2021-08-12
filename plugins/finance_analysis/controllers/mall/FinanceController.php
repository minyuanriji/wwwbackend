<?php

namespace app\plugins\finance_analysis\controllers\mall;

use app\plugins\Controller;
use app\plugins\finance_analysis\forms\mall\FinanceIncomeStatForm;

class FinanceController extends Controller{

    public function actionAnalysis(){

        return $this->render('analysis');
    }

    /**
     * 收支统计
     * @return \yii\web\Response
     */
    public function actionIncomeStat(){
        $form = new FinanceIncomeStatForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->get());
    }
}