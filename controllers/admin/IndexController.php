<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 后台首页
 * Author: zal
 * Date: 2020-04-08
 * Time: 16:12
 */

namespace app\controllers\admin;


use app\core\ApiCode;
use app\forms\admin\AdminForm;
use app\forms\admin\AdminEditForm;
use app\forms\common\AttachmentForm;
use app\forms\mall\user\UserForm;
use app\helpers\SerializeHelper;
use app\logic\AuthLogic;
use app\models\Admin;

class IndexController extends BaseController
{
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new AdminForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->getAdminList());
            }
        } else {
            return $this->render('index');
        }
    }

    /**
     * 管理员用户信息
     * @return \yii\web\Response
     */
    public function actionUser()
    {
        /* @var Admin $admin */
        $admin = Admin::find()->where(['id' => \Yii::$app->admin->id])->one();
        $newUser = [
            'max_mall_num' => $admin->admin_type == 1 ? '无限制' :
                ($admin->mall_num == -1 ? '无限制' : $admin->mall_num),
            'username' => $admin->username,
            'identity' => $admin->toArray()
        ];

        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'admin_info' => $newUser,
            ]
        ]);
    }

    /**
     * 账户编辑
     * @return array|string|\yii\web\Response
     */
    public function actionEdit()
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
            return $this->render('edit');
        }
    }

    /**
     * 管理员账号删除
     * @return \yii\web\Response
     */
    public function actionDestroy()
    {
        $form = new AdminForm();
        $form->id = \Yii::$app->request->post('id');
        $res = $form->destroy();

        return $this->asJson($res);
    }

    /**
     * 修改密码
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionEditPassword()
    {
        $form = new AdminForm();
        $form->attributes = \Yii::$app->request->post();
        $res = $form->editPassword();

        return $this->asJson($res);
    }

    /**
     * 当前账号修改密码
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionAdminEditPassword()
    {
        $form = new AdminForm();
        $form->attributes = \Yii::$app->request->post();
        $form->id = \Yii::$app->admin->id;
        $res = $form->editPassword();

        return $this->asJson($res);
    }

    public function actionPermissions()
    {
        return $this->asJson([
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'permissions' => AuthLogic::getPermissionsList(),
                'storage' => AttachmentForm::getCommon()->getStorage()
            ]
        ]);
    }

    public function actionMe()
    {
        return $this->render('me');
    }

    public function actionCloudAccount()
    {
        $userNum = AuthLogic::getChildrenNum();
        //$res = \Yii::$app->cloud->auth->getAuthInfo();
        $res['host']['account_num'] = '-1';
        $accountNum = $res['host']['account_num'];

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'account_num' => $accountNum,
                'user_num' => $userNum
            ]
        ];
    }

}
