<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-08
 * Time: 14:30
 */

namespace app\plugins\stock\controllers\mall;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\Goods;
use app\models\GoodsWarehouse;

use app\plugins\stock\forms\mall\StockGoodsDeleteForm;
use app\plugins\stock\forms\mall\StockGoodsForm;
use app\plugins\Controller;
use app\plugins\stock\forms\mall\StockGoodsListForm;
use app\plugins\stock\models\StockGoods;
use app\plugins\stock\models\StockLevel;

class GoodsController extends Controller
{

    public function actionAgentSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new StockGoodsForm();
                return $this->asJson($form->getAgentSetting());
            }
        }
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new StockGoodsListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('index');
        }
    }


    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = \Yii::$app->request->post();
                $stock_goods = StockGoods::findOne(['mall_id' => \Yii::$app->mall->id, 'goods_id' => $form['goods_id'], 'is_delete' => 0, 'goods_type' => 0]);
                if (!$stock_goods) {
                    $stock_goods = new StockGoods();
                    $stock_goods->mall_id = \Yii::$app->mall->id;
                    $stock_goods->goods_id = $form['goods_id'];
                }
                $stock_goods->origin_price = $form['origin_price'];
                $stock_goods->agent_price = SerializeHelper::encode($form['level_list']);
                $stock_goods->equal_level_list = SerializeHelper::encode($form['equal_level_list']);
                $stock_goods->fill_level_list = SerializeHelper::encode($form['fill_level_list']);
                $stock_goods->over_level_list = SerializeHelper::encode($form['over_level_list']);
                if (!$stock_goods->save()) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '保存失败', 'error' => $stock_goods->getErrors()]);
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功']);
            } else {
                $level_list = StockLevel::find()->where(['is_delete' => 0, 'is_use' => 1, 'mall_id' => \Yii::$app->mall->id])->select('level,name')->asArray()->all();
                $equal_level_list = StockLevel::find()->where(['is_equal' => 1, 'is_delete' => 0, 'is_use' => 1, 'mall_id' => \Yii::$app->mall->id])->select('level,name')->asArray()->all();
                $id = \Yii::$app->request->get('stock_goods_id');
                $stock_goods = StockGoods::findOne(['mall_id' => \Yii::$app->mall->id, 'id' => $id, 'is_delete' => 0, 'goods_type' => 0]);
                if ($stock_goods) {
                    $agent_price_list = [];
                    if (!empty($stock_goods->agent_price) && $stock_goods->agent_price != "null") {
                        $agent_price_list = SerializeHelper::decode($stock_goods->agent_price);
                    }
                    foreach ($level_list as &$price) {
                        $price['stock_price'] = 0;
                        if(!empty($agent_price_list)){
                            foreach ($agent_price_list as $agent_price) {
                                if ($agent_price['level'] == $price['level']) {
                                    $price['stock_price'] = $agent_price['stock_price'];
                                }
                            }
                        }
                    }
                    $stock_goods->agent_price = $level_list;
                    $equal_level_price_list = [];
                    if (!empty($stock_goods->equal_level_list) && $stock_goods->equal_level_list != "null") {
                        $equal_level_price_list = SerializeHelper::decode($stock_goods->equal_level_list);
                    }
                    foreach ($equal_level_list as &$price) {
                        $price['equal_price'] = 0;
                        if(!empty($equal_level_price_list)){
                            foreach ($equal_level_price_list as $equal_price) {
                                if ($equal_price['level'] == $price['level']) {
                                    $price['equal_price'] = $equal_price['equal_price'];
                                }
                            }
                        }
                    }
                    $stock_goods->equal_level_list = $equal_level_list;
                    $fill_level_list = StockLevel::find()->where(['is_fill' => 1, 'is_delete' => 0, 'is_use' => 1, 'mall_id' => \Yii::$app->mall->id])->select('level,name')->asArray()->all();
                    $fill_level_price_list = [];
                    if (!empty($stock_goods->fill_level_list) && $stock_goods->fill_level_list != "null") {
                        $fill_level_price_list = SerializeHelper::decode($stock_goods->fill_level_list);
                    }
                    foreach ($fill_level_list as &$price) {
                        $price['fill_price'] = 0;
                        if(!empty($fill_level_price_list)){
                            foreach ($fill_level_price_list as $fill_price) {
                                if ($fill_price['level'] == $price['level']) {
                                    $price['fill_price'] = $fill_price['fill_price'];
                                }
                            }
                        }
                    }
                    $stock_goods->fill_level_list = $fill_level_list;

                    $over_level_list = StockLevel::find()->where(['is_over' => 1, 'is_delete' => 0, 'is_use' => 1, 'mall_id' => \Yii::$app->mall->id])->select('level,name')->asArray()->all();
                    $over_level_price_list = [];
                    if (!empty($stock_goods->over_level_list) && $stock_goods->over_level_list != "null") {
                        $over_level_price_list = SerializeHelper::decode($stock_goods->over_level_list);
                    }
                    foreach ($over_level_list as &$price) {
                        $price['over_price'] = 0;
                        if(!empty($over_level_price_list)){
                            foreach ($over_level_price_list as $over_price) {
                                if ($over_price['level'] == $price['level']) {
                                    $price['over_price'] = $over_price['over_price'];
                                }
                            }
                        }
                    }
                    $stock_goods->over_level_list = $over_level_list;

                    $goods = Goods::findOne($stock_goods->goods_id);
                    if ($goods) {
                        $goodsWarehouse = $goods->goodsWarehouse;
                        return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '获取成功', 'data' => ['stock_goods' => $stock_goods, 'goods' => $goods, 'goods_warehouse' => $goodsWarehouse]]);
                    }
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '获取成功', 'data' => ['stock_goods' => $stock_goods, 'goods' => $goods]]);
            }
        }
    }

    public function actionGoodsSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new StockGoodsForm();
                $form->attributes = \Yii::$app->request->post()['form'];
                return $this->asJson($form->saveAgentGoodsSetting());
            } else {
                $form = new StockGoodsForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getAgentGoodsSetting());
            }
        } else {
            return $this->render('edit');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {

        if (\Yii::$app->request->isAjax) {

            if (\Yii::$app->request->isPost) {
                $form = new StockGoodsDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

}