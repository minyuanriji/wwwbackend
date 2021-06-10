<?php

namespace app\plugins\boss\controllers\mall;

use app\core\ApiCode;
use app\forms\mall\user\UserForm;
use app\plugins\boss\forms\mall\BossAwardsListForm;
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
                $form = new BossAwardsListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        }

        return $this->render('index');
    }

    /**
     * @Note:奖金池编辑
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BossAwardsListForm();
                $post_data = \Yii::$app->request->post();
                return $this->asJson($form->save($post_data));
            } elseif (\Yii::$app->request->isGet) {
                $form = new BossAwardsListForm();
                $id = \Yii::$app->request->get('id');
                return $this->asJson($form->getDetail($id));
            }
        }
        return $this->render('edit');
    }

    /**
     * @Note:奖金池编辑
     * @return string|\yii\web\Response
     */
    public function actionIsEnable()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BossAwardsListForm();
                $post_data = \Yii::$app->request->post();
                return $this->asJson($form->isEnable($post_data));
            }
        }
    }

    /**
     * @Note::奖金池删除
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BossAwardsListForm();
            $id = \Yii::$app->request->post('id');
            return $this->asJson($form->del($id));
        }
    }

    /**
     * @Note:奖池充值
     * @return \yii\web\Response
     */
    public function actionRecharge()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BossAwardsListForm();
            $params = \Yii::$app->request->post();
            return $this->asJson($form->recharge($params));
        }
    }

    /**
     * @Note:获取平台用户
     * @return \yii\web\Response
     */
    public function actionPlatformUsers()
    {
        if (\Yii::$app->request->isGet) {
            $form = new UserForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getPlatformList());
        }
    }

    /**
     * @Note:修改奖池用户
     * @return \yii\web\Response
     */
    public function actionUserEdit()
    {
        if (\Yii::$app->request->isPost) {
            $form = new BossAwardsListForm();
            $params = \Yii::$app->request->post();
            return $this->asJson($form->userEdit($params));
        }
    }

}