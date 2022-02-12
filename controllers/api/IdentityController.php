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
use app\forms\api\identity\IdentityCheckAuthForm;
use app\forms\api\identity\LoginForm;
use app\forms\api\identity\RegisterForm;
use app\forms\api\identity\SmsForm;
use app\forms\api\identity\WechatForm;
use app\forms\api\user\UserBindForm;
use app\helpers\ArrayHelper;
use app\models\ErrorLog;
use app\models\Mall;
use phpDocumentor\Reflection\Types\Integer;
use yii;
use app\controllers\business\GetAttentionWeChat;

class IdentityController extends ApiController
{
    /**
     * 验证授权TOKEN
     * @Author: lin
     * @Date: 2022-02-11
     * @Time: 11:55
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionCheckAuth()
    {
        $form = new IdentityCheckAuthForm();
        $form->attributes = $this->requestData;
        return $this->asJson($form->check());
    }

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
        $headers = \Yii::$app->request->headers;
        $stands_mall_id = isset($headers["x-stands-mall-id"]) ? $headers["x-stands-mall-id"] : 0;
        $result = $wechatForm->wxAuthorized($stands_mall_id);
        return $result;
    }

    public function actionSubscribeStatus(){
        $data = $this->requestData;
        $result = (new GetAttentionWeChat()) -> getUserAttentionWeChatInfo($data['user_id']);
        return $this -> asJson($result);
    }

    /**
     * 微信公众号授权（使用中--------------------------------------------------------------------）
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
        $headers = \Yii::$app->request->headers;
        $stands_mall_id = isset($headers["x-stands-mall-id"]) ? $headers["x-stands-mall-id"] : 0;
        $result = $wechatForm->authorized($stands_mall_id);
        return $result;
    }

    /**
     * 小程序授权登录
     */
    public function actionMiniLogin ()
    {
        $wechatForm = new WechatForm();
        $wechatForm->attributes = $this->requestData;
        $parent_user_id = !empty($this->requestData['parent_user_id']) ? $this->requestData['parent_user_id'] : 0;
        $parent_source = !empty($this->requestData['parent_source']) ? $this->requestData['parent_source'] : null;
        $headers = \Yii::$app->request->headers;
        $stands_mall_id = isset($headers["x-stands-mall-id"]) ? $headers["x-stands-mall-id"] : 5;
        $result = $wechatForm->miniAuthorized($parent_user_id,$parent_source,$stands_mall_id);
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
     * 微信公众号授权完成绑定手机号（-------------------------------------------------------）
     * @return array
     * @throws \Exception
     */
    public function actionBind(){
        $smsForm = new UserBindForm();
        $smsForm->attributes = $this->requestData;
        $recommend_id = !empty($this->requestData['recommend_id']) ? $this->requestData['recommend_id'] : 0;
        $headers = \Yii::$app->request->headers;
        $stands_mall_id = isset($headers["x-stands-mall-id"]) ? $headers["x-stands-mall-id"] : 5;
        if (!$recommend_id) {
            if($stands_mall_id != 5){
                $mall = Mall::findOne([['id' => $stands_mall_id], ['is_delete' => 0], ['is_recycle' => 0], ['is_disable' => 0]]);
                if ($mall) {
                    $recommend_id = $mall->user_id;
                } else {
                    //$recommend_id = 9;
                }
            } else {
                //$recommend_id = 9;
            }
        }
        return $smsForm->bind($recommend_id,$stands_mall_id);
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