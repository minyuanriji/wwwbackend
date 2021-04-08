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
use app\forms\api\user\GiveScoreForm;
use app\forms\api\user\UserAddressForm;
use app\forms\api\user\UserEditForm;
use app\forms\api\user\UserForm;
use app\forms\api\user\UserRechargeForm;
use app\forms\common\attachment\CommonAttachment;
use app\controllers\business\{Qrcode,Poster,NewUserIntegral};
use app\models\user\User;

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
     * 清空分享海报图片
     */
    public function delCodeImg(){
        $path = \Yii::$app->basePath . '/web/temp/';
        $path_wj = opendir($path);
        while (false !== ($file_name = readdir($path_wj))){
            if(!is_dir($path . $file_name)){
                unlink($path . $file_name);
            }
        }
        closedir($path_wj);
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
        return $shareForm->get('pages/index/index');
    }

    public function actionLinkPoster2(){
        $access_token = (new SetToken()) -> getToken();
        //1.4 拼接扫码跳转的小程序url地址
        $path= '/pages/user/index?user_id='. \Yii::$app->user->identity ->id;
        $width=430;
        //1.5 拼接地址+二维码大小
        $post_data='{"path":"'.$path.'","width":'.$width.'}';
        //1.6 拼接获取二维码的地址带上access_token
        $url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
        //1.7 发送 POST请求换取二维码
        $Img = '/runtime/image/poster/images/' .time() . uniqid() . '.jpg';
        $filename = \Yii::$app->basePath .$Img;
        $result= (new SetToken()) -> httpRequest($url,$post_data,'POST');
        file_put_contents($filename, $result);
//        $data='image/png;base64,'.base64_encode($result);
//        var_dump($data);
//        echo '<img src="data:'.$data.'">';
        $data = $this -> actionLinkPoster($filename);
        return $this -> asJson($data);
    }

    /**
     * 分享领取海报领取红包
     * @return array
     */
    public function actionLinkPoster($flag = ''){
        $qrCodeData = '';
        $WeChatCode = '';
        if(empty($flag)){
            $code = \Yii::$app->request->hostInfo . '/h5/#/pages/public/login?user_id='. \Yii::$app->user->identity ->id;
            $qrCodeData = QRcode::pngData($code,13);
        }else{
            $WeChatCode = $flag;
        }
        $config = array(
            'bg_url' => \Yii::$app->basePath . '/web/statics' . '/bg/redpack.png',//背景图片路径
            'text' => array(
//                array(
//                    'text' => '初夏',//文本内容
//                    'left' => 312, //左侧字体开始的位置
//                    'top' => 676, //字体的下边框
//                    'fontSize' => 16, //字号
//                    'fontColor' => '255,0,0', //字体颜色
//                    'angle' => 0,
//                ),
//                array(
//                    'text' => '你不运动，地球也会动',
//                    'left' => 310,
//                    'top' => 720,
//                    'width' => 400,
//                    'fontSize' => 16, //字号
//                    'fontColor' => '0,0,80', //字体颜色
//                    'angle' => 0,
//                ),
//                array(
//                    'text' => '好嗨哟~',
//                    'left' => 507,
//                    'top' => 760,
//                    'width' => 400,
//                    'fontSize' => 25, //字号
//                    'fontColor' => '0,175,80', //字体颜色
//                    'angle' => -50,
//                ),
                array(
                    'text' => '扫码领红包',
                    'left' => 116,
                    'top' => 280,
                    'width' => 300,
                    'fontSize' => 14, //字号
                    'fontColor' => '0,0,80', //字体颜色
                    'angle' => 0,
                ),
            ),
            'image' => array(
//                array(
//                    'name' => 'jobs', //图片名称，用于出错时定位
//                    'url' => './img/02.jpg',
//                    'stream' => 0, //图片资源是否是字符串图像流
//                    'left' => 202,
//                    'top' => 639,
//                    'right' => 0,
//                    'bottom' => 0,
//                    'width' => 100,
//                    'height' => 100,
//                    'radius' => 50,
//                    'opacity' => 100
//                ),
//                array(
//                    'name' => '水印', //图片名称，用于出错时定位
//                    'url' => './img/03.png',
//                    'stream' => 0,
//                    'left' => 507,
//                    'top' => 590,
//                    'right' => 0,
//                    'bottom' => 0,
//                    'width' => 108,
//                    'height' => 108,
//                    'radius' => 0,
//                    'opacity' => 100
//                ),
                array(
                    'name' => '二维码', //图片名称，用于出错时定位
                    'url' => $WeChatCode,
                    'stream' => $qrCodeData,
                    'left' => 118,
                    'top' => 300,
                    'right' => 0,
                    'bottom' => 0,
                    'width' => 90,
                    'height' => 90,
                    'radius' => 0,
                    'opacity' => 100
                ),
//                array(
//                    'name' => '苹果', //图片名称，用于出错时定位
//                    'url' => './img/01.jpg',
//                    'stream' => 0,
//                    'left' => 335,
//                    'top' => 910,
//                    'right' => 0,
//                    'bottom' => 0,
//                    'width' => 50,
//                    'height' => 50,
//                    'radius' => 0,
//                    'opacity' => 100
//                ),
            )
        );
        Poster::setConfig($config);
//设置保存路径
        $Img = '/runtime/image/poster/images/' .time() . uniqid() . '.jpg';
        $filename = \Yii::$app->basePath .$Img;
        $res = Poster::make($filename);
        if($res){
            $data = [
                'status' => 1,
                'img' => \Yii::$app->request->hostInfo . $Img,
                'msg' => '正在生成分享海报！'
            ];
            //是否要清理缓存资源
            Poster::clear();
            if(empty($flag)){
                return $this->asJson($data);
            }
            return $data;
        }
        //是否要清理缓存资源
        Poster::clear();
    }

    /**
     * 新人获取红包福利
     */
    public function actionGetIntegral(){
        $form = new GiveScoreForm();
        $form->attributes = $this->requestData;
        $form->user_id = \Yii::$app->user->id;
        $form->number  = 300;
        $form->desc    = "新人领取300积分";
        return $form->execute();
    }

}
