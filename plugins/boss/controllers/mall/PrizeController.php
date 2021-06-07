<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:10
 */

namespace app\plugins\boss\controllers\mall;


use app\core\ApiCode;
use app\plugins\Controller;

use app\plugins\boss\forms\common\BossLevelCommon;
use app\plugins\boss\forms\mall\BossLevelDeleteForm;
use app\plugins\boss\forms\mall\BossLevelEditForm;
use app\plugins\boss\forms\mall\BossLevelEnableListForm;
use app\plugins\boss\forms\mall\BossLevelListForm;

use app\plugins\boss\models\BossLevel;


class PrizeController extends Controller
{


    /**
     * @Note:奖金池列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new BossLevelListForm();
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
                $form = new BossLevelEnableListForm();
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
                $form = new BossLevelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new BossLevelEditForm();
                $form->attributes=\Yii::$app->request->get();
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
        $level = BossLevel::findOne(['id' => \Yii::$app->request->post('id'), 'is_delete' => 0]);
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
                $form = new BossLevelDeleteForm();
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
                'weights' => BossLevelCommon::getInstance()->getLevelWeights(),

            ]
        ]);
    }

}