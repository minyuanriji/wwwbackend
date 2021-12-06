<?php

namespace app\plugins\taolijin\controllers\mall;

use app\plugins\Controller;
use app\plugins\taolijin\forms\mall\TaoLiJinAliGetCatForm;
use app\plugins\taolijin\forms\mall\TaoLiJinCatAllListForm;
use app\plugins\taolijin\forms\mall\TaoLiJinCatChildrenListForm;
use app\plugins\taolijin\forms\mall\TaoLiJinCatDeleteForm;
use app\plugins\taolijin\forms\mall\TaoLiJinCatDetailForm;
use app\plugins\taolijin\forms\mall\TaoLiJinCatEditForm;
use app\plugins\taolijin\forms\mall\TaoLiJinCatListForm;
use app\plugins\taolijin\forms\mall\TaoLiJinCatSortForm;

class CatController extends Controller{

    /**
     * 分类管理
     * @return string|\yii\web\Response
     */
    public function actionIndex(){
        return $this->render('index');
    }

    /**
     * 查询数据
     * @return \yii\web\Response
     */
    public function actionGetList(){
        $form = new TaoLiJinCatListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 添加、编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new TaoLiJinCatEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new TaoLiJinCatDetailForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        } else {
            return $this->render('edit');
        }
    }

    public function actionAllList(){
        $form = new TaoLiJinCatAllListForm();
        $form->attributes = \Yii::$app->request->get();
        $res = $form->getAllList();
        return $res;
    }

    /**
     * 查找子分类
     * @return \yii\web\Response
     */
    public function actionChildrenList(){
        $form = new TaoLiJinCatChildrenListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getChildrenList());
    }

    public function actionSort(){
        $form = new TaoLiJinCatSortForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->storeSort());
    }

    /**
     * 删除
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new TaoLiJinCatDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->destroy());
    }

    /**
     * 获取联盟分类
     * @return \yii\web\Response
     */
    public function actionGetAliCat(){
        $form = new TaoLiJinAliGetCatForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->get());
    }
}