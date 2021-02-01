<?php
namespace app\mch\controllers;


use app\core\ApiCode;
use app\mch\forms\common\goods\PluginMchGoods;
use app\mch\forms\goods\GoodsEditForm;
use app\mch\forms\goods\GoodsForm;
use app\mch\forms\goods\GoodsListForm;
use app\mch\forms\goods\LabelListForm;

class GoodsController extends MchController {

    /**
     * 商品列表
     * @return bool|string|\yii\web\Response
     */
    public function actionIndex(){

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new GoodsListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->get('search');
                $res = $form->getList();
                return $this->asJson($res);
            }
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $form = new GoodsListForm();
                $form->flag = \Yii::$app->request->post('flag');
                $chooseList = \Yii::$app->request->post('choose_list');
                $form->choose_list = $chooseList ? explode(',', $chooseList) : [];
                $form->getList();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    public function actionEdit(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $data = \Yii::$app->request->post();

                $form = new GoodsEditForm();
                $form->mch_id = \Yii::$app->mchAdmin->identity->mchModel->id;

                $data_form = json_decode($data['form'], true);

                $form->attributes = $data_form;

                $form->attrGroups = json_decode($data['attrGroups'], true);

                $res = $form->save();
                return $this->asJson($res);
            } else {
                $form = new GoodsForm();
                $form->attributes = \Yii::$app->request->get();
                $res = $form->getDetail();

                return $this->asJson($res);
            }
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 商品标签
     */
    public function actionLabel(){

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new LabelListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->get('search');
                $res = $form->search();
                return $this->asJson($res);
            }
        }

        return $this->render('label');
    }

    /**
     * 更新商品名称
     * @return \yii\web\Response
     */
    public function actionUpdateGoodsName(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->updateGoodsName();

        return $this->asJson($res);
    }

    /**
     * 排序
     * @return \yii\web\Response
     */
    public function actionEditSort(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->editSort();
        return $this->asJson($res);
    }

    /**
     * 商户商品申请上架
     * @return \yii\web\Response
     */

    public function actionApplyStatus(){
        $form = new PluginMchGoods();
        $form->goods_id = \Yii::$app->request->post('id');
        $form->mch_id   = \Yii::$app->mchAdmin->identity->mchModel->id;

        return $this->asJson($form->applyStatus());
    }

    /**
     * 批量更新状态
     * @return \yii\web\Response
     */
    public function actionBatchUpdateStatus(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateStatus();

        return $this->asJson($res);
    }

    /**
     * 批量设置运费
     * @return \yii\web\Response
     */
    public function actionBatchUpdateFreight(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateFreight();

        return $this->asJson($res);
    }

    /**
     * 批量限购
     * @return \yii\web\Response
     */
    public function actionBatchUpdateConfineCount(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchUpdateConfineCount();

        return $this->asJson($res);
    }

    /**
     * 商品删除
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();

        return $this->asJson($res);
    }

    /**
     * 批量删除
     * @return \yii\web\Response
     */
    public function actionBatchDestroy(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->batchDestroy();
        return $this->asJson($res);
    }

    /**
     * 导出excel
     * @return \yii\web\Response
     */
    public function actionExportGoodsList(){
        if (\Yii::$app->request->post('flag') === 'EXPORT') {
            $form = new GoodsListForm();
            $form->flag = \Yii::$app->request->post('flag');
            $form->choose_list = \Yii::$app->request->post('choose_list');
            $res = $form->getList();
            return $this->asJson($res);
        }
    }

    /**
     * 上下架
     * @return \yii\web\Response
     */
    public function actionSwitchStatus(){
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->switchStatus();

        return $this->asJson($res);
    }
}