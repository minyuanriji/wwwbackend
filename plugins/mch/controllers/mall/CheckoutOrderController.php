<?php
namespace app\plugins\mch\controllers\mall;

use app\plugins\Controller;
use app\plugins\mch\forms\mall\CheckoutOrderDetailForm;
use app\plugins\mch\forms\mall\CheckoutOrderSearchForm;
use app\plugins\mch\forms\mall\CheckoutOrderQueryReloadForm;

class CheckoutOrderController extends Controller{

    public function actionIndex(){

        if (\Yii::$app->request->isAjax) {
            $form = new CheckoutOrderSearchForm();
            $form->attributes = \Yii::$app->getRequest()->get();
            return $this->asJson($form->search());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    //订单详情
    public function actionDetail(){
        if (\Yii::$app->request->isAjax) {
            $form = new CheckoutOrderDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('detail');
        }
    }

    //查询状态
    public function actionQueryReload(){
        $form = new CheckoutOrderQueryReloadForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->execute());
    }

    //账单记录统计
    public function actionBillStatistics(){
        $form = new CheckoutOrderSearchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->statistics());
    }
}
