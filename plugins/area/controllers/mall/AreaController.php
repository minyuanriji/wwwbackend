<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 15:47
 */


namespace app\plugins\area\controllers\mall;

use app\core\ApiCode;
use app\plugins\area\forms\api\AreaApplyForm;
use app\plugins\area\forms\mall\ApplyListForm;
use app\plugins\area\forms\mall\AreaSettingForm;
use app\plugins\area\forms\mall\IncomeListForm;
use app\plugins\area\models\AreaAgent;
use app\plugins\area\models\AreaApply;
use app\plugins\Controller;
use app\plugins\area\forms\mall\AreaGoodsForm;
use app\plugins\area\forms\mall\AreaListForm;
use app\plugins\area\forms\mall\AreaRemarksForm;
use app\plugins\area\forms\mall\AreaUserEditForm;


class AreaController extends Controller
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * Date: 2020-05-10
     * Time: 21:35
     * @Note:区域代理列表
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AreaListForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new AreaListForm();
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
                $form = new AreaRemarksForm();
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
            $form = new AreaUserEditForm();
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
            $form = new AreaUserEditForm();
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
            $form = new AreaUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->changeLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-11
     * @Time: 16:56
     * @Note:批量修改区域代理等级
     * @return \yii\web\Response
     */
    public function actionBatchLevel()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new AreaUserEditForm();
            $form->attributes = \Yii::$app->request->post();
            return $this->asJson($form->batchLevel());
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-10
     * @Time: 9:18
     * @Note:区域代理设置
     * @return string|\yii\web\Response
     */

    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AreaSettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new AreaSettingForm();
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
     * @Date: 2020-07-22
     * @Time: 16:08
     * @Note:审核通过
     */
    public function actionPass()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new AreaSettingForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new AreaSettingForm();
                return $this->asJson($form->search());
            }
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-22
     * @Time: 16:08
     * @Note:申请列表
     */
    public function actionApply()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isGet) {
                $form = new ApplyListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        }
        return $this->render('apply-list');
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-22
     * @Time: 16:08
     * @Note:申请列表
     */
    public function actionApplyDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $apply_id = \Yii::$app->request->post('id');
                $apply = AreaApply::findOne($apply_id);
                if (!$apply) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '申请不存在！']);
                }
                $apply->is_delete = 1;
                if (!$apply->save()) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
            }
        }

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

                $agent = AreaAgent::findOne(['id' => $id, 'is_delete' => 0]);
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