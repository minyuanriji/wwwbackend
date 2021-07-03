<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-10
 * Time: 20:19
 */

namespace app\controllers\mall;


use app\core\ApiCode;
use app\forms\admin\AdminEditForm;
use app\forms\admin\AdminForm;
use app\forms\mall\goods\LabelEditForm;
use app\forms\mall\goods\LabelForm;
use app\forms\mall\goods\LabelListForm;
use app\models\Label;

class MirrorController extends MallController
{
    /**
     * @Note:子账号列表
     */
    public function actionSonMallList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new AdminForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getAdminList());
            }
        } else {
            return $this->render('son-mall-list');
        }
    }


    /**
     * @Note:标签编辑
     */
    public function actionSonMallEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $formData = \Yii::$app->request->post('form');

                $form = new AdminEditForm();
                $form->admin_id = isset($formData['id']) ? $formData['id'] : '';
                $form->attributes = $formData;
                $form->attributes = $formData['adminInfo'];
                $form->permissions = \Yii::$app->request->post('permissions');
                $form->isCheckExpired = \Yii::$app->request->post('isCheckExpired');
                $form->isAppMaxCount = \Yii::$app->request->post('isAppMaxCount');
                $res = $form->save(1);
                return $res;
            } else {
                $form = new AdminForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('son-mall-edit');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:43
     * @Note:商品标签删除
     */
    public function actionSonMallDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $label = Label::findOne(['id' => \Yii::$app->request->post('id'), 'is_delete' => 0]);
                if (!$label) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该数据不存在或已被删除！']);
                }
                $label->is_delete = 1;
                if ($label->save()) {
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！']);
            }
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:43
     * @Note:商品标签
     */
    public function actionSonAccountList()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new LabelListForm();
                $form->attributes = \Yii::$app->request->get();
                $form->attributes = \Yii::$app->request->get('search');
                $res = $form->search();
                return $this->asJson($res);
            }
        }

        return $this->render('son-account-list');
    }


    /**
     * 账户编辑
     * @return array|string|\yii\web\Response
     */
    public function actionSonAccountEdit()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $formData = \Yii::$app->request->post('form');

                $form = new AdminEditForm();
                $form->admin_id = isset($formData['id']) ? $formData['id'] : '';
                $form->attributes = $formData;
                $form->attributes = $formData['adminInfo'];
                $form->permissions = \Yii::$app->request->post('permissions');
                $form->isCheckExpired = \Yii::$app->request->post('isCheckExpired');
                $form->isAppMaxCount = \Yii::$app->request->post('isAppMaxCount');
                $res = $form->save();
                return $res;
            } else {
                $form = new AdminForm();
                $form->attributes = \Yii::$app->request->get();
                $detail = $form->getDetail();
                return $this->asJson($detail);
            }
        } else {
            return $this->render('son-account-edit');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-29
     * @Time: 17:43
     * @Note:商品标签删除
     */
    public function actionSonAccountDelete()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $label = Label::findOne(['id' => \Yii::$app->request->post('id'), 'is_delete' => 0]);
                if (!$label) {
                    return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '该数据不存在或已被删除！']);
                }
                $label->is_delete = 1;
                if ($label->save()) {
                    return $this->asJson(['code' => ApiCode::CODE_SUCCESS, 'msg' => '删除成功！']);
                }
                return $this->asJson(['code' => ApiCode::CODE_FAIL, 'msg' => '删除失败！']);
            }
        }
    }


}