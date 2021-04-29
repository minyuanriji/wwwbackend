<?php
namespace app\plugins\commission\controllers\mall;

use app\plugins\commission\forms\mall\CommissionRuleListForm;
use app\plugins\commission\forms\mall\CommissionRuleDetailForm;
use app\plugins\commission\forms\mall\CommissionRuleEditForm;
use app\plugins\commission\forms\mall\SearchGoodsForm;
use app\plugins\commission\forms\mall\SearchStoreForm;
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
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CommissionRuleEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }else{
                $form = new CommissionRuleDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        }else{
            return $this->render('edit');
        }
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

    /**
     * 加载门店
     * @return string|yii\web\Response
     */
    public function actionSearchStore(){
        $form = new SearchStoreForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }
}