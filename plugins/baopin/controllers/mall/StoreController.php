<?php
namespace app\plugins\baopin\controllers\mall;


use app\plugins\baopin\forms\mall\StoreClerkLogsListForm;
use app\plugins\baopin\forms\mall\StoreDeleteGoods;
use app\plugins\baopin\forms\mall\StoreGoodsListForm;
use app\plugins\baopin\forms\mall\StoreGoodsStockEditForm;
use app\plugins\baopin\forms\mall\StoreListForm;
use app\plugins\Controller;

class StoreController extends Controller{

    /**
     * 门店列表
     * @return string|yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new StoreListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 门店爆品管理
     * @return string|yii\web\Response
     */
    public function actionGoodsList(){
        if (\Yii::$app->request->isAjax) {
            $form = new StoreGoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('goods_list');
        }
    }

    /**
     * 修改门店爆品库存
     * @return string|yii\web\Response
     */
    public function actionSaveStock(){
        $form = new StoreGoodsStockEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除门店爆品
     * @return string|yii\web\Response
     */
    public function actionDeleteGoods(){
        $form = new StoreDeleteGoods();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 核销记录
     * @return string|yii\web\Response
     */
    public function actionClerkLogsList(){
        if (\Yii::$app->request->isAjax) {
            $form = new StoreClerkLogsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('clerk_logs_list');
        }
    }
}