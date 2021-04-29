<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 14:13
 */

namespace app\controllers\api;

use app\forms\api\IndexForm;
use app\forms\mall\data_statistics\TimingStatisticsForm;
use app\models\User;
use app\models\UserInfo;
use function EasyWeChat\Kernel\Support\get_client_ip;

class IndexController extends ApiController
{
    public function actionIndex()
    {
        $form = new IndexForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->getIndex());
    }


    //时间段调用
    public function actionUpdateHour(){
        $user = new User();
        $mobile = $user::find()->where(['mall_id'=>5])->orderBy('mobile desc')->select(['mobile'])->one()['mobile'];
        $mobile = empty($mobile)?'18384385624':$mobile+1;
        $mobile = (string)$mobile;
        $password = '123456';
        $user->username = $mobile;
        $user->mobile = $mobile;
        $user->mall_id = 5;
        $user->access_token = \Yii::$app->security->generateRandomString();
        $user->auth_key = \Yii::$app->security->generateRandomString();
        $user->nickname = "";
        $user->password = \Yii::$app->getSecurity()->generatePasswordHash($password);
        $user->avatar_url = "";
        $user->last_login_at = time();
        $user->login_ip = get_client_ip();
        $user->parent_id = 0;
        $user->second_parent_id = 0;
        $user->third_parent_id = 0;
        if (!$user->save()) {
            return ['msg'=>'添加失败1'];
        }
        $userInfoModel = new UserInfo();
        $userInfoModel->mall_id = 5;
        $userInfoModel->mch_id = 0;
        $userInfoModel->user_id = $user->id;
        $userInfoModel->unionid = "";
        $userInfoModel->openid = "";
        $userInfoModel->platform_data = "";
        $userInfoModel->platform = 'h5';
        if (!$userInfoModel->save()) {
            return ['msg'=>'添加失败2'];
        }
        return ['msg'=>'注册成功','access_token' => $user->access_token,'mobile'=>$mobile];
    }
}