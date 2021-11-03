<?php

namespace app\plugins\seckill\controllers\mall\goods;

use app\plugins\Controller;
use app\plugins\seckill\forms\mall\seckill_goods\MallGoodsSearchForm;
use app\plugins\seckill\forms\mall\seckill_goods\SeckillGoodsDelForm;
use app\plugins\seckill\forms\mall\seckill_goods\SeckillGoodsDelSkuForm;
use app\plugins\seckill\forms\mall\seckill_goods\SeckillGoodsListForm;
use app\plugins\seckill\forms\mall\seckill_goods\SeckillGoodsSaveForm;
use app\plugins\seckill\forms\mall\seckill_goods\SeckillGoodsSkuSearchForm;

class SeckillGoodsController extends Controller
{
    /**
     * @Note:秒杀商品列表
     * @return string|\yii\web\Response
     */
    public function actionList()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SeckillGoodsListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->search());
        } else {
            return $this->render('list');
        }
    }

    /**
     * @Note:秒杀商品保存
     * @return string|\yii\web\Response
     */
    public function actionSeckillGoodsSave()
    {
        $form = new SeckillGoodsSaveForm();
        $form->attributes = \Yii::$app->request->post('goods');
        return $this->asJson($form->save());
    }


    /**
     * @Note:删除秒杀商品
     * @return \yii\web\Response
     */
    public function actionSeckillGoodsDel()
    {
        $form = new SeckillGoodsDelForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();

        return $this->asJson($res);
    }

    /**
     * @Note:删除秒杀商品规格
     * @return \yii\web\Response
     */
    public function actionSeckillGoodsSkuDel()
    {
        $form = new SeckillGoodsDelSkuForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->delete();

        return $this->asJson($res);
    }

    /**
     * @Note:查询商城商品及规格
     * @return \yii\web\Response
     */
    public function actionSearchMallGoodsSku()
    {
        $form = new SeckillGoodsSkuSearchForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->search();

        return $this->asJson($res);
    }

    /**
     * @Note:查询商城积分商品
     * @return \yii\web\Response
     */
    public function actionMallGoods()
    {
        $form = new MallGoodsSearchForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->search();

        return $this->asJson($res);
    }

}