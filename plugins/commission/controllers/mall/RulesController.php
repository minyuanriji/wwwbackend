<?php
namespace app\plugins\commission\controllers\mall;

use app\plugins\commission\forms\CommissionRuleListForm;
use app\plugins\commission\forms\mall\SearchGoodsForm;
use app\plugins\Controller;

class RulesController extends Controller{

    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new CommissionRuleListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    public function actionEdit(){

        return $this->render('edit');
    }

    /**
     * 加载商品
     * @return string|yii\web\Response
     */
    public function actionSearchGoods(){
        $form = new SearchGoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }
}