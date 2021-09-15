<?php

namespace app\plugins\alibaba\controllers\mall;

use app\plugins\alibaba\forms\mall\AlibabaDistributionAlibabaGoodsGearchForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionAliGoodsImportForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionCategoryListForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionDeleteCategoryForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionDeleteGoodsForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionEditCategoryForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionEditCategorySortForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionGetCategoryForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionGoodsBatchSaveForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionGoodsListForm;
use app\plugins\alibaba\forms\mall\AlibabaDistributionSyncCategoryForm;
use app\plugins\Controller;

class DistributionController extends Controller{

    /**
     * 商品批量编辑保存
     * @return string|\yii\web\Response
     */
    public function actionGoodsBatchSave(){
        $form = new AlibabaDistributionGoodsBatchSaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 阿里巴巴商品导入
     * @return string|\yii\web\Response
     */
    public function actionAliGoodsImport(){
        $form = new AlibabaDistributionAliGoodsImportForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->import());
    }

    /**
     * 获取分类
     * @return string|\yii\web\Response
     */
    public function actionGetCategory(){
        $form = new AlibabaDistributionGetCategoryForm();
        return $this->asJson($form->get());
    }

    /**
     * 删除商品
     * @return string|\yii\web\Response
     */
    public function actionDeleteGoods(){
        $form = new AlibabaDistributionDeleteGoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 商品列表
     * @return string|\yii\web\Response
     */
    public function actionGoodsList(){
        if (\Yii::$app->request->isAjax) {
            $form = new AlibabaDistributionGoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('list');
        }
    }

    /**
     * 阿里巴巴商品搜索
     * @return string|\yii\web\Response
     */
    public function actionAlibabaGoodsSearch(){
        $form = new AlibabaDistributionAlibabaGoodsGearchForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getList());
    }

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