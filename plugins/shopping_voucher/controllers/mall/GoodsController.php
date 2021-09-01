<?php

namespace app\plugins\shopping_voucher\controllers\mall;

use app\plugins\shopping_voucher\forms\mall\BatchDeleteForm;
use app\plugins\shopping_voucher\forms\mall\DeleteForm;
use app\plugins\shopping_voucher\forms\mall\DeleteGoodsForm;
use app\plugins\shopping_voucher\forms\mall\GoodsListForm;
use app\plugins\shopping_voucher\forms\mall\IntegraSettingUpdateForm;
use app\plugins\shopping_voucher\forms\mall\SaveForm;
use app\plugins\shopping_voucher\forms\mall\ScoreSettingUpdateForm;
use app\plugins\shopping_voucher\forms\mall\SearchGoodsForm;
use app\plugins\Controller;

class GoodsController extends Controller
{
    public function actionList()
    {
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
     * @return
     */
    public function actionSave()
    {
        $form = new SaveForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->save());
    }

    /**
     * 批量删除爆品记录
     * @return
     */
    public function actionBatchDeleteGoods()
    {
        $form = new BatchDeleteForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->deleteMuti());
    }

    /**
     * 删除爆品记录
     * @return
     */
    public function actionDeleteGoods()
    {
        $form = new DeleteGoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->delete());
    }

    /**
     * 加载商品
     * @return
     */
    public function actionSearchGoods()
    {
        $form = new SearchGoodsForm();
        $form->attributes = \Yii::$app->request->post();
        return $this->asJson($form->search());
    }

}