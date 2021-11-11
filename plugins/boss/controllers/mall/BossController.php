<?php

namespace app\plugins\boss\controllers\mall;

use app\core\ApiCode;
use app\plugins\boss\forms\mall\BossSettingForm;
use app\plugins\boss\forms\mall\IncomeListForm;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardMember;
use app\plugins\Controller;
use app\plugins\boss\forms\mall\BossListForm;
use app\plugins\boss\forms\mall\BossRemarksForm;
use app\plugins\boss\forms\mall\BossUserEditForm;

class BossController extends Controller
{
    /**
     * @Note:股东列表
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            if (\Yii::$app->request->post('flag') === 'EXPORT') {
                $fields = explode(',', \Yii::$app->request->post('fields'));
                $form = new BossListForm();
                $form->attributes = \Yii::$app->request->post();
                $form->fields = $fields;
                $form->getList();
                return false;
            } else {
                return $this->render('index');
            }
        }
    }

    /**
     * @Note:修改备注
     */
    public function actionRemarksEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BossRemarksForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

    /**
     * @Note:查找用户
     */
    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossUserEditForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getUser());
        }
    }


    /**
     * @Note:
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->save());
        } else {
            return $this->render('edit');
        }
    }


    /**
     * @Note:修改等级
     * @return \yii\web\Response
     */
    public function actionLevelChange()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeLevel());
        }
    }

    //更新股东等级
    public function actionSaveLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->saveLevel());
        }
    }


    /**
     * @Note:批量修改股东等级
     * @return \yii\web\Response
     */
    public function actionBatchLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BossUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchLevel());
        }
    }


    /**
     * @Note:股东设置
     * @return string|\yii\web\Response
     */

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BossSettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new BossSettingForm();
                return $this->asJson($form->search());
            }
        }
        return $this->render('setting');
    }

    /**
     * @Note:提成明细
     */
    public function actionIncomeList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new IncomeListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('income-list');
    }

    /**
     * @Note:经销商删除
     */
    public function actionDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

                $id = \Yii::$app->request->post('id');

                $transaction = \Yii::$app->db->beginTransaction();
                $agent = Boss::findOne(['id' => $id, 'is_delete' => 0]);
                if (!$agent) {
                    $transaction->rollBack();
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该股东不存在或者已被删除！']);
                }

                BossAwardMember::deleteAll(['user_id' => $agent->user_id]);

                $agent->is_delete=1;
                if(!$agent->save()){
                    $transaction->rollBack();
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！','error'=>$agent->getErrors()]);
                }
                $transaction->commit();
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
            }
        }
    }

    /**
     * @Note:分红明细
     */
    public function actionBonusList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new IncomeListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('bonus-list');
    }


}