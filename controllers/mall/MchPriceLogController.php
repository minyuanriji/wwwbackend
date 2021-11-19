<?php
namespace app\controllers\mall;

use app\forms\mall\finance\MchPriceLogApplyForm;
use app\forms\mall\finance\MchPriceLogListForm;

class MchPriceLogController extends MallController {

    /**
     * 结算记录
     * @return string|\yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new MchPriceLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 确认操作
     * @return string|\yii\web\Response
     */
    public function actionApply(){
        $form = new MchPriceLogApplyForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->doApply());
    }

}