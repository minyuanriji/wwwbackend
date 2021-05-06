<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:47
 */


namespace app\plugins\boss\controllers\mall;

use app\core\ApiCode;
use app\plugins\boss\forms\mall\BossSettingForm;
use app\plugins\boss\forms\mall\IncomeListForm;
use app\plugins\boss\models\Boss;
use app\plugins\Controller;
use app\plugins\boss\forms\mall\BossGoodsForm;
use app\plugins\boss\forms\mall\BossListForm;
use app\plugins\boss\forms\mall\BossRemarksForm;
use app\plugins\boss\forms\mall\BossUserEditForm;


class BossController extends Controller
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 21:35
     * @Note:股东列表
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BossListForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new BossListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('index');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 21:36
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 15:36
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 15:45
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
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


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:18
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:19
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-21
     * @Time: 20:02
     * @Note:经销商删除
     */
    public function actionDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {

                $id = \Yii::$app->request->post('id');

                $agent = Boss::findOne(['id' => $id, 'is_delete' => 0]);
                if (!$agent) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该股东不存在或者已被删除！']);
                }
                $agent->is_delete=1;
                if(!$agent->save()){
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！','error'=>$agent->getErrors()]);
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
            }
        }
    }


}