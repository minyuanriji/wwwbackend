<?php
namespace app\plugins\giftpacks\controllers\mall;

use app\plugins\Controller;
use app\plugins\giftpacks\forms\mall\GiftpacksDeleteForm;
use app\plugins\giftpacks\forms\mall\GiftpacksDeleteItemForm;
use app\plugins\giftpacks\forms\mall\GiftpacksEditForm;
use app\plugins\giftpacks\forms\mall\GiftpacksGoodsListForm;
use app\plugins\giftpacks\forms\mall\GiftpacksItemListForm;
use app\plugins\giftpacks\forms\mall\GiftpacksListForm;
use app\plugins\giftpacks\forms\mall\GiftpacksSaveItemForm;
use app\plugins\giftpacks\forms\mall\GiftpacksSearchStoreForm;

class GiftpacksController extends Controller{

    /**
     * 大礼包管理
     * @return string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new GiftpacksListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 搜索门店
     * @return string|\yii\web\Response
     */
    public function actionSearchStore(){
        $form = new GiftpacksSearchStoreForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->search());
    }

    /**
     * 保存大礼包信息
     * @return \yii\web\Response
     */
    public function actionEdit(){
        $form = new GiftpacksEditForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 搜索选择商品
     * @return \yii\web\Response
     */
    public function actionGoodsList(){
        $form = new GiftpacksGoodsListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 删除大礼包
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new GiftpacksDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 保存礼包商品
     * @return \yii\web\Response
     */
    public function actionSaveItem(){
        $form = new GiftpacksSaveItemForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 礼包商品记录
     * @return \yii\web\Response
     */
    public function actionItemList(){
        $form = new GiftpacksItemListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 删除大礼包商品
     * @return \yii\web\Response
     */
    public function actionDeleteItem(){
        $form = new GiftpacksDeleteItemForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}