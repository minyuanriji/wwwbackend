<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-07-28
 * Time: 15:47
 */


namespace app\plugins\business_card\controllers\mall;

use app\plugins\business_card\forms\mall\BusinessCardAuthForm;
use app\plugins\Controller;
use app\plugins\distribution\forms\mall\BusinessCardDepartmentForm;
use app\plugins\distribution\forms\mall\DistributionListForm;
use app\plugins\distribution\forms\mall\DistributionRemarksForm;
use app\plugins\distribution\forms\mall\DistributionUserEditForm;


class BusinessCardAuthController extends Controller
{

    /**
     * @Author: 广东七件事 zal
     * Date: 2020-05-10
     * Time: 21:35
     * @Note:新增
     * @return string|\yii\web\Response
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new DistributionListForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            } else {
                $form = new DistributionListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getList());
            }
        }
        return $this->render('index');
    }

    /**
     * @Author: 广东七件事 zal
     * Date: 2020-05-10
     * Time: 21:36
     * @Note:增加用户权限
     */
    public function actionAdd()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BusinessCardAuthForm();
                $form->form_data = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-07-28
     * @Time: 20:36
     * @Note:查找用户
     */
    public function actionSearchUser()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BusinessCardAuthForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getUser());
        }
    }


    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-05-11
     * @Time: 15:45
     * @Note:
     * @return string|\yii\web\Response
     */
    public function actionEdit()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BusinessCardAuthForm();
            $form->form_data = \Yii::$app->request->post();
            return $this->asJson($form->edit());
        } else {
            return $this->render('edit');
        }
    }

    /**
     * 职位列表
     * @Author: 广东七件事 zal
     * @Date: 2020-07-21
     * @Time: 19:29
     * @return \yii\web\Response
     */
    public function actionPositionList()
    {
        $form = new BusinessCardAuthForm();
        $form->attributes = \Yii::$app->request->get();
        return $this->asJson($form->getPositionList());
    }

    /**
     * 查看部门用户列表
     * @Author: 广东七件事 zal
     * @Date: 2020-07-21
     * @Time: 19:29
     * @return \yii\web\Response
     */
    public function actionLookUser()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new BusinessCardAuthForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->getAuthUserList());
            } else {
                $form = new BusinessCardAuthForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getAuthUserList());
            }
        }
        return $this->render('user_list');
    }

}