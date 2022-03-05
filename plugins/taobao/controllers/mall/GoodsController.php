<?php

namespace app\plugins\taobao\controllers\mall;

use app\plugins\Controller;
use app\plugins\taobao\forms\mall\TaobaoGoodsDetailForm;
use app\plugins\taobao\forms\mall\TaobaoGoodsListForm;
use app\plugins\taobao\forms\mall\TaobaoGoodsRemoteImportForm;
use app\plugins\taobao\forms\mall\TaobaoGoodsRemoteSearchForm;

class GoodsController extends Controller{

    /**
     * 商品列表
     * @return string|\yii\web\Response
     */
    public function actionIndex(){
        if (\Yii::$app->request->isAjax) {
            $form = new TaobaoGoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 获取详情
     * @return \yii\web\Response
     */
    public function actionDetail(){
        $form = new TaobaoGoodsDetailForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->getDetail());
    }

    /**
     * 搜索淘宝联盟商品
     * @return \yii\web\Response
     */
    public function actionRemoteSearch(){
        $form = new TaobaoGoodsRemoteSearchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**
     * 导入淘宝联盟商品
     * @return \yii\web\Response
     */
    public function actionRemoteImport(){
        $form = new TaobaoGoodsRemoteImportForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->import());
    }
}