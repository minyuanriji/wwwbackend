<?php

namespace app\plugins\mch\controllers\mall;

use app\plugins\Controller;
use app\plugins\mch\forms\mall\MchForm;
use app\plugins\mch\forms\mall\MchGroupAddItemForm;
use app\plugins\mch\forms\mall\MchGroupDeleteForm;
use app\plugins\mch\forms\mall\MchGroupDeleteItemForm;
use app\plugins\mch\forms\mall\MchGroupDetailForm;
use app\plugins\mch\forms\mall\MchGroupForm;
use app\plugins\mch\forms\mall\MchGroupItemListForm;
use app\plugins\mch\forms\mall\MchGroupNewForm;

class GroupController extends Controller{

    /**
     * 商户连锁店管理
     * @return string|\yii\web\Response
     */
    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new MchGroupForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 商户连锁店编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            $form = new MchGroupDetailForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getDetail());
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 新增连锁信息
     * @return \yii\web\Response
     */
    public function actionNew(){
        $form = new MchGroupNewForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new MchGroupDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 搜索商户
     * @return \yii\web\Response
     */
    public function actionSearchMch(){
        $form = new MchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 获取子店列表
     * @return \yii\web\Response
     */
    public function actionItemList(){
        $form = new MchGroupItemListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 添加子店铺
     * @return \yii\web\Response
     */
    public function actionAddItem(){
        $form = new MchGroupAddItemForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除子店铺
     * @return \yii\web\Response
     */
    public function actionDeleteItem(){
        $form = new MchGroupDeleteItemForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}