<?php
namespace app\plugins\baopin\controllers\mall;

use app\plugins\baopin\forms\mall\BatchDeleteForm;
use app\plugins\baopin\forms\mall\DeleteForm;
use app\plugins\baopin\forms\mall\GoodsListForm;
use app\plugins\baopin\forms\mall\IntegraSettingUpdateForm;
use app\plugins\baopin\forms\mall\SaveForm;
use app\plugins\baopin\forms\mall\ScoreSettingUpdateForm;
use app\plugins\baopin\forms\mall\SearchGoodsForm;
use app\plugins\Controller;

class GoodsController extends Controller{

    public function actionList(){
        if (\Yii::$app->request->isAjax) {
            $form = new GoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * 保存爆品信息
     * @return string|yii\web\Response
     */
    public function actionSave(){
        $form = new SaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 删除爆品记录
     * @return string|yii\web\Response
     */
    public function actionBatchDeleteGoods(){
        $form = new BatchDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->deleteMuti());
    }

    /**
     * 删除爆品记录
     * @return string|yii\web\Response
     */
    public function actionDeleteGoods(){
        $form = new DeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
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
     * 保存积分赠送设置
     * @return string|yii\web\Response
     */
    public function actionUpdateScoreSetting(){
        $form = new ScoreSettingUpdateForm();
        $post = \Yii::$app->request->post();
        $form->attributes = array_merge(["goods_id" => $post['goods_id']], $post['form']);
        return $this->asJson($form->save());
    }

    /**
     * 保存红包赠送设置
     * @return string|yii\web\Response
     */
    public function actionUpdateIntegraSetting(){
        $form = new IntegraSettingUpdateForm();
        $post = \Yii::$app->request->post();
        $form->attributes = array_merge(["goods_id" => $post['goods_id']], $post['form']);
        return $this->asJson($form->save());
    }
}