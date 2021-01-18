<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户接口类
 * Author: zal
 * Date: 2020-04-24
 * Time: 12:01
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\identity\ForgetPasswordForm;
use app\forms\api\identity\SmsForm;
use app\forms\api\poster\PosterForm;
use app\forms\api\user\UserAddressForm;
use app\forms\api\user\UserEditForm;
use app\forms\api\user\UserForm;
use app\forms\api\user\UserRechargeForm;
use app\forms\common\attachment\CommonAttachment;

class UserController extends ApiController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
                'ignore' => ['config']
            ],
        ]);
    }

    /**
     * 获取用户信息
     * @Author: zal
     * @Date: 2020-05-07
     * @Time: 14:33
     * @return array
     */
    public function actionUserInfo()
    {
        $form = new UserForm();
        return $form->getBasicInfo();
    }

    /**
     * 修改
     * @Author: zal
     * @Date: 2020-05-07
     * @Time: 14:33
     * @return \yii\web\Response
     */
    public function actionUpdate(){
        $form = new UserEditForm();
        $form->attributes = $this->requestData;
        return $form->edit();
    }

    /**
     * 设置交易密码
     * @Author: zal
     * @Date: 2020-05-07
     * @Time: 15:20
     * @return yii\web\Response
     * @throws \Exception
     */
    public function actionSetTransactionPassword(){
        $forgetPasswordForm = new ForgetPasswordForm();
        $forgetPasswordForm->attributes = $this->requestData;
        return $forgetPasswordForm->setTransactionPassword();
    }

    /**
     * 收货地址
     * @return mixed
     */
    public function actionUserAddress()
    {
        $form = new UserAddressForm();
        $form->limit = isset($this->requestData["limit"])?$this->requestData["limit"]:20;
        $form->hasCity = $this->requestData;
        return $form->getList();
    }

    /**
     * 设置默认收货地址
     * @return array
     */
    public function actionUserAddressDefault()
    {
        $form = new UserAddressForm();
        $form->attributes = $this->requestData;
        return $form->setDefaultAddress();
    }

    /**
     * @return mixed
     */
    public function actionUserAddressDetail()
    {
        $form = new UserAddressForm();
        $form->id = $this->requestData;
        return $form->detail();
    }

    /**
     * 删除地址
     * @return array
     */
    public function actionUserAddressDelete()
    {
        $form = new UserAddressForm();
        $form->attributes = $this->requestData;
        return $form->destroy();
    }

    /**
     * 保存地址
     * @return array
     */
    public function actionUserAddressSave()
    {
        $form = new UserAddressForm();
        $form->attributes = $this->requestData;
        return $form->save();
    }

    /**
     * 余额明细
     * @return array|bool
     */
    public function actionBalanceDetail(){
        $form = new UserForm();
        if(isset($this->requestData["page"]) && !empty($this->requestData["page"])){
            $form->page = $this->requestData["page"];
        }
        if(isset($this->requestData["limit"]) && !empty($this->requestData["limit"])){
            $form->limit = $this->requestData["limit"];
        }
        $form->attributes = $this->requestData;
        return $form->balanceLog();
    }

    /**
     * 积分明细
     * @return array|bool
     */
    public function actionScoreDetail(){
        $form = new UserForm();
        $form->attributes = $this->requestData;
        return $form->scoreLog();
    }

    /**
     * 获取省市区数据
     * @return \yii\web\Response
     */
    public function actionAddressInfo()
    {
        $form = new UserAddressForm();
        return $this->asJson($form->autoAddressInfo());
    }

    //根据微信地址获取数据库省市区数据
    public function actionWechatDistrict()
    {
        $form = new WechatDistrictForm();
        $form->attributes = \Yii::$app->request->get();
        return $form->getList();
    }

    /**
     * 绑定手机号
     * @return array
     * @throws \Exception
     */
    public function actionBindPhone()
    {
        $form = new SmsForm();
        $form->attributes = $this->requestData;
        return $form->bindUserMobile();
    }

    /**
     * 上传头像图片（仅上传，不修改头像数据）
     * @return array
     */
    public function actionUpload(){
        $admin_id = \Yii::$app->user->id;
        //来源1后台2前台
        $from = 2;
        $result = CommonAttachment::addAttachmentInfo($from,$this->mall_id,$admin_id);
        return $result;
    }

    /**
     * 充值配置
     * @return array|\ArrayObject|mixed
     */
    public function actionRechargeConfig(){
        $form = new UserRechargeForm();
        $form->attributes = $this->requestData;
        return $form->getRechargeSetting();
    }

    /**
     * 商品海报
     * @return \yii\web\R1esponse
     * @throws \Exception
     */
    public function actionPoster(){
        $form = new PosterForm();
        $shareForm = $form->share();
        $shareForm->sign = "share/";
        return $shareForm->get();
    }
}
