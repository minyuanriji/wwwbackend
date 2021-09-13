<?php

namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\forms\mall\AlibabaDistributionCategoryListForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionDeleteCategoryForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionEditCategoryForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionEditCategorySortForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionSyncCategoryForm;
use app\plugins\Controller;

class DistributionController extends Controller{

    /**
     * 同步分类
     * @return string|\yii\web\Response
     */
    public function actionSyncCategory(){
        $form = new AlibabaDistributionSyncCategoryForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->sync());
    }

    /**
     * 分类列表
     * @return string|\yii\web\Response
     */
    public function actionCategoryList(){
        $form = new AlibabaDistributionCategoryListForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

    /**
     * 保存分类排序
     * @return string|\yii\web\Response
     */
    public function actionEditCategorySort(){
        $form = new AlibabaDistributionEditCategorySortForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 编辑分类
     * @return string|\yii\web\Response
     */
    public function actionEditCategory(){
        $form = new AlibabaDistributionEditCategoryForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除分类
     * @return string|\yii\web\Response
     */
    public function actionDeleteCategory(){
        $form = new AlibabaDistributionDeleteCategoryForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }
}