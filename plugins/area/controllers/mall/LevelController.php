<?php

namespace app\plugins\area\controllers\mall;

use app\core\ApiCode;
use app\plugins\Controller;

use app\plugins\area\forms\common\AreaLevelCommon;
use app\plugins\area\forms\mall\AreaLevelDeleteForm;
use app\plugins\area\forms\mall\AreaLevelEditForm;
use app\plugins\area\forms\mall\AreaLevelEnableListForm;
use app\plugins\area\forms\mall\AreaLevelListForm;

use app\plugins\area\models\AreaLevel;

class LevelController extends Controller
{
    /**
     * @Note:等级列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

            } elseif (\Yii::$app->request->isGet) {
                $form = new AreaLevelListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        }
        return $this->render('index');
    }


    /**
     * @Note:编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AreaLevelEditForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } elseif (\Yii::$app->request->isGet) {
                $form = new AreaLevelEditForm();
                $form->attributes=\Yii::$app->request->get();
                return $this->asJson($form->getDetail());
            }
        }
        return $this->render('edit');

    }


    /**
     * @Note:等级状态变更
     * @return \yii\web\Response
     */
    public function actionSwitchStatus()
    {
        $level = AreaLevel::findOne(['id' => \Yii::$app->request->post('id'), 'is_delete' => 0]);
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
     * @Note:删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AreaLevelDeleteForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }


    /**
     * @Note:获取分销配置以及权重
     * @return \yii\web\Response
     */
    public function actionSetting()
    {
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'weights' => AreaLevelCommon::getInstance()->getLevelWeights(),

            ]
        ]);
    }

}