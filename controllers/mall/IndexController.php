<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-06
 * Time: 11:49
 */

namespace app\controllers\mall;

use app\core\ApiCode;
use app\models\Admin;
use app\plugins\mch\forms\mall\MchMallSettingForm;

class IndexController extends MallController
{

    /**
     * 首页
     * @Author: zal
     * @Date: 2020-04-10
     * @Time: 09:50
     * @return array
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $admin = Admin::find()->where(['id' => \Yii::$app->admin->id])->asArray()->one();
            return $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'mall' => \Yii::$app->mall,
                    'user_identity' => $admin
                ],
            ]);
        } else {
            return $this->render('../data-statistics/index');
        }
    }

    /**
     * 商城头部导航
     * @Author: zal
     * @Date: 2020-04-08
     * @Time: 15:33
     * @return array
     * @throws \Exception
     */
    public function actionHeaderBar()
    {
        /** @var Admin $admin */
        $admin = \Yii::$app->admin->identity;
        $form = new \app\forms\common\RoleSettingForm();
        $setting = $form->getSettingInfo();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'user' => [
                    'id' => $admin->id,
                    'mch_id' => $admin->mch_id,
                    'username' => $admin->username,
                    'admin_type' => $admin->admin_type,
                ],
                'mall' => [
                    'id' => \Yii::$app->mall->id,
                    'name' => \Yii::$app->mall->name,
                ],
                'navs' => \Yii::$app->plugin->getHeaderNavs(),
                'update_password_status' => $setting['update_password_status']
            ],
        ];
    }

    public function actionEdit()
    {
        return $this->render('edit');
    }

    public function actionMallPermissions()
    {
        $permissions = \Yii::$app->role->getAccountPermission();
        if (\Yii::$app->admin->identity->mch_id) {
            /** @var MchMallSetting $setting */
            $permissions = [];
            $setting = (new MchMallSettingForm())->search();
            if ($setting->is_distribution) {
                $permissions[] = 'area';
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'permissions' => $permissions
            ]
        ];
    }

    public function actionRule()
    {
        return $this->render('rule');
    }

    public function actionRole()
    {
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => \Yii::$app->role->name
        ];
    }

}