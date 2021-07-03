<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:10
 */

namespace app\plugins\stock\controllers\mall;


use app\core\ApiCode;
use app\models\Goods;
use app\plugins\Controller;

use app\plugins\stock\forms\common\StockLevelCommon;
use app\plugins\stock\forms\mall\StockLevelDeleteForm;
use app\plugins\stock\forms\mall\StockLevelEditForm;
use app\plugins\stock\forms\mall\StockLevelEnableListForm;
use app\plugins\stock\forms\mall\StockLevelListForm;

use app\plugins\stock\forms\mall\UpgradeBagForm;
use app\plugins\stock\forms\mall\UpgradeBagListForm;
use app\plugins\stock\models\StockLevel;
use app\plugins\stock\models\UpgradeBag;


class LevelController extends Controller
{


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:等级列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new StockLevelListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        }

        return $this->render('index');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 22:26
     * @Note:已经启用的等级
     */
    public function actionEnableList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new StockLevelEnableListForm();
                return $this->asJson($form->getList());
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:23
     * @Note:编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new StockLevelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new StockLevelEditForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        }
        return $this->render('edit');

    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:52
     * @Note:等级状态变更
     * @return \yii\web\Response
     */
    public function actionSwitchStatus()
    {
        $level = StockLevel::findOne(['id' => \Yii::$app->request->post('id'), 'is_delete' => 0]);
        if (!$level) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => '该等级不存在或已被删除！',
            ]);
        }
        try {
            if ($level->is_use) {
                $level->is_use = 0;
            } else {
                $level->is_use = 1;
            }
            if ($level->save()) {
                return $this->asJson([
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '分销等级状态变更成功',
                ]);
            }
        } catch (\Exception $exception) {
            return $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ]);
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
                $form = new StockLevelDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-12
     * @Time: 9:47
     * @Note:获取分销配置以及权重
     * @return \yii\web\Response
     */
    public function actionSetting()
    {

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'weights' => StockLevelCommon::getInstance()->getLevelWeights(),

            ]
        ]);
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-31
     * @Time: 14:22
     * @Note: 礼包详情
     */
    public function actionUpgradeBagDetail()
    {
        if (\Yii::$app->request->isAjax) {
            $bag_id = (\Yii::$app->request->get('bag_id'));
            $bag = UpgradeBag::findOne(['is_delete' => 0, 'id' => $bag_id]);
            if (!$bag) {
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '找不到配置']);
            }
            $goods = $bag->goods;
            return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '', 'data' => ['bag' => $bag, 'goods' => $goods->goodsWarehouse]]);

        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-31
     * @Time: 14:22
     * @Note:升级礼包
     */
    public function actionUpgradeBag()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new UpgradeBagForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new UpgradeBagListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        } else {
            return $this->render('upgrade-bag');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-31
     * @Time: 14:22
     * @Note:升级礼包
     */
    public function actionUpgradeBagDelete()
    {
        if (\Yii::$app->request->isAjax) {
            $bag_id = (\Yii::$app->request->get('id'));
            $bag = UpgradeBag::findOne(['is_delete' => 0, 'id' => $bag_id]);
            if (empty($bag)) {
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '找不到配置']);
            }
            $bag->is_delete=1;
            if(!$bag->save()){
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除失败',['error'=>$bag->getErrors()]]);
            }
            return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功']);

        }
    }

}