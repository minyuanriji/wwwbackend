<?php
namespace app\plugins\commission\controllers\mall;

use app\plugins\commission\forms\mall\CommissionRuleOpenForm;
use app\plugins\commission\forms\mall\CommissionRuleDeleteForm;
use app\plugins\commission\forms\mall\CommissionRuleListForm;
use app\plugins\commission\forms\mall\CommissionRuleDetailForm;
use app\plugins\commission\forms\mall\CommissionRuleEditForm;
use app\plugins\commission\forms\mall\SearchAddcreditForm;
use app\plugins\commission\forms\mall\SearchGoodsForm;
use app\plugins\commission\forms\mall\SearchHotelForm;
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
     * 删除规则
     * @return string|yii\web\Response
     */
    public function actionDelete(){
        $form = new CommissionRuleDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 打开或者关闭商品独立分佣规则
     * @return string|yii\web\Response
     */
    public function actionCommissionGoodsOpen(){
        $form = new CommissionRuleOpenForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->open());
    }

    /**
     * 打开或者关闭门店独立分佣规则
     * @return string|yii\web\Response
     */
    public function actionCommissionStoreOpen(){
        $form = new CommissionRuleOpenForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->open());
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

    /**
     * 加载酒店
     * @return string|yii\web\Response
     */
    public function actionSearchHotel(){
        $form = new SearchHotelForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

    /**
     * 加载话费平台
     * @return string|yii\web\Response
     */
    public function actionSearchAddcredit(){
        $form = new SearchAddcreditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }
}