<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户身份操作接口类
 * Author: zal
 * Date: 2020-04-25
 * Time: 12:01
 */

namespace app\controllers\api;

use app\forms\api\identity\ForgetPasswordForm;
use app\forms\api\identity\LoginForm;
use app\forms\api\identity\RegisterForm;
use app\forms\api\identity\SmsForm;
use app\forms\api\identity\WechatForm;
use app\forms\api\user\UserBindForm;
use app\helpers\ArrayHelper;
use app\models\ErrorLog;
use yii;

class IdentityController extends ApiController
{
    /**
     * 登录
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionLogin(){
        $form = new LoginForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $res = $form->login();
        return $this->asJson($res);
    }

    /**
     * 授权登录注册
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function actionWxAuthLogin()
    {
        $wechatForm = new WechatForm();
        $wechatForm->attributes = $this->requestData;
        $result = $wechatForm->wxAuthorized();
        return $result;
    }

    /**
     * 授权登录
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function actionAuthLogin()
    {
        $wechatForm = new WechatForm();
        $wechatForm->attributes = $this->requestData;
        $result = $wechatForm->authorized();
        return $result;
    }

    /**
     * 注册
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionRegister(){
        $form = new RegisterForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $res = $form->register();
        return $this->asJson($res);
    }

    /**
     * 获取上级信息
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionParentMember(){
        $form = new RegisterForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $res = $form->parentInfo();
        return $this->asJson($res);
    }

    /**
     * 绑定上级
     * @Author: vita
     * @Date: 2020-12-27
     * @Time: 10:33
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionBindParent(){
        $form = new RegisterForm();
        $form->attributes = $this->requestData;
        $form->mall_id = \Yii::$app->mall->id;
        $res = $form->bindParent();
        return $this->asJson($res);
    }

    /**
     * 获取手机验证码
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionPhoneCode(){
        $smsForom = new SmsForm();
        $smsForom->attributes = $this->requestData;
        return $this->asJson($smsForom->getPhoneCode());
    }

    /**
     * 忘记密码
     * @Author: zal
     * @Date: 2020-04-29
     * @Time: 10:20
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionForgetPassword(){
        $forgetPasswordForm = new ForgetPasswordForm();
        $forgetPasswordForm->attributes = $this->requestData;
        return $this->asJson($forgetPasswordForm->forgetPassword());
    }

    /**
     * 绑定
     * @return array
     * @throws \Exception
     */
    public function actionBind(){
        $smsForm = new UserBindForm();
        $smsForm->attributes = $this->requestData;
        $user_id = !empty($this->requestData['user_id']) ? $this->requestData['user_id'] : 0;
        return $smsForm->bind($user_id);
    }

    /**
     * 小程序授权登录
     */
    public function actionMiniLogin(){
        $wechatForm = new WechatForm();
        $wechatForm->attributes = $this->requestData;
        $parent_user_id = !empty($this->requestData['parent_user_id']) ? $this->requestData['parent_user_id'] : 0;
        $result = $wechatForm->miniAuthorized($parent_user_id);
        return $result;
    }

    /**
     * 小程序授权手机号
     */
    public function actionAuthPhone(){
        $wechatForm = new WechatForm();
        $wechatForm->attributes = $this->requestData;
        $result = $wechatForm->authorizedMobilePhone();
        return $result;
    }
}