<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * api登录类
 * Author: zal
 * Date: 2020-04-27
 * Time: 15:16
 */

namespace app\forms\api\identity;

use app\component\caches\UserCache;
use app\core\ApiCode;
use app\events\UserEvent;
use app\forms\common\WechatCommon;

use app\helpers\ArrayHelper;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\ErrorLog;
use app\models\Mall;
use app\models\User;
use app\models\user\User as UserMode;
use app\models\UserInfo;
use app\plugins\mpwx\models\MpwxConfig;
use EasyWeChat\Factory;
use jianyan\easywechat\Wechat;
use function EasyWeChat\Kernel\Support\str_random;
use function EasyWeChat\Kernel\Support\get_client_ip;
use app\models\mysql\{UserParent,UserChildren,QrcodeParameter};
class WechatForm extends BaseModel
{
    public $code;
    public $encryptedData;
    public $iv;
    public $user_id;

    public function rules()
    {
        return [
            [['code','encryptedData','iv'],'string'],
            [['user_id'],"integer"]
        ];

    }

    /**
     * 微信授权登录注册
     * @Author: zal
     * @Date: 2020-12-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function wxAuthorized($stands_mall_id)
    {
        $returnData = [];
        /** @var Wechat $wechatModel */
        $wechatModel = \Yii::$app->wechat;

        if($wechatModel->isWechat)
        {
            $result = $wechatModel->app->oauth->user();

            if(!empty($result)){
                $userInfo = $result->original;

                $phoneConfig = AppConfigLogic::getPhoneConfig();
                //没有开启全网通，则直接入库，如果开启了，返回给前端
                if(empty($phoneConfig["all_network_enable"])){
                    $returnData = $this->userHandle($userInfo,$stands_mall_id);
                    if(empty($returnData)){
                        return $this->returnApiResultData(ApiCode::CODE_FAIL,'授权失败');
                    }
                }else{
                    $returnData = ["access_token" => ""];
                    //检测是否授权
                    $result = UserLogic::checkIsAuthorized($userInfo,0,$stands_mall_id);
                    //$result = $this->userHandle($userInfo);
                    \Yii::warning("wechatForm authorized result:".var_export($result,true));
                    if(!empty($result)){
                        //开始登录用户
                        \Yii::$app->user->login($result);
                        $returnData["access_token"] = $result->access_token;
                    }else{
                        //开始注册用户
                        $res = $this->registerx($userInfo);
                    }
                }
            }
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$returnData);
    }

    /**
     * 微信授权
     * @Author: zal   2
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function authorized($stands_mall_id)
    {
        try {
            $returnData = [];
            /** @var Wechat $wechatModel */
            $wechatModel = \Yii::$app->wechat;
            if($wechatModel->isWechat)
            {
                $authData = $wechatModel->app->oauth->user();
                \Yii::warning("授权结果 result:".json_encode($authData));
                \Yii::warning("stands_mall_id result:".json_encode($stands_mall_id));
                if(!empty($authData)){
                    $authOriginalData = $authData->original;
                    $phoneConfig = AppConfigLogic::getPhoneConfig();
                    \Yii::warning("全网通 result:".json_encode($phoneConfig));
                    //没有开启全网通，则直接入库，如果开启了，返回给前端
                    if(empty($phoneConfig["all_network_enable"])){
                        $returnData = $this->userHandle($authOriginalData,$stands_mall_id);
                        if(empty($returnData)){
                            throw new \Exception('授权失败');
                        }
                    }else{
                        $returnData = ["access_token" => ""];

                        $oauth =  $authData;

                        //检测是否授权
                        $result = UserLogic::checkIsAuthorized($authOriginalData,0,$stands_mall_id);
                        if($result){
                            \Yii::$app->user->login($result);
                            $returnData["access_token"] = $result->access_token;
                        }else{
                            //将获得的数据存入缓存，key为openid加密字符串
                            $randStr = str_random(6);
                            $openid = md5($authOriginalData["openid"] . $randStr);
                            \Yii::$app->cache->set($openid, $authOriginalData);
                            // $returnData['access_token'] = $oauth->token;
                            $returnData["key"]    = $openid;
                            $returnData["config"] = $phoneConfig;
                        }
                    }
                }
            }
            \Yii::warning("最终结果 result:".json_encode($returnData));
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$returnData);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }

    /**
     * 微信小程序授权登录
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function miniAuthorized($parent_user_id = '',$parent_source = '',$stands_mall_id = 0){
        try{
            if(!empty($parent_source)){
                $source_data = (new QrcodeParameter()) -> getParentData($parent_source);
                $source_data = json_decode($source_data['data'],true);
                $parent_user_id = $source_data['pid'];
            }
            /** @var Wechat $wechatModel */
            $wechatModel = \Yii::$app->wechat;
            //微信小程序授权登录
            if ($stands_mall_id) {
                $info = MpwxConfig::findOne(['mall_id' => $stands_mall_id, 'is_delete' => 0]);
                if ($info) {
                    \Yii::$app->params['wechatMiniProgramConfig'] = [
                        'app_id'     => $info->app_id,
                        'secret'     => $info->secret,
                    ];
                }
            }
            $resultData = $wechatModel->miniProgram->auth->session($this->code);
            \Yii::warning("miniAuthorized resultData=".json_encode($resultData));
            if(isset($resultData["errcode"]) && $resultData["errcode"] != 0){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,$resultData["errmsg"]);
            }
            //授权成功获取sessiong_key
            $sessionKey = $resultData["session_key"];
            $iv = $this->iv;
            $encrypted = $this->encryptedData;
            //解密微信加密数据
            $data = $wechatModel->miniProgram->encryptor->decryptData($sessionKey,$iv,$encrypted);
            \Yii::warning("miniAuthorized data=".json_encode($data));
            $data["openid"]      = $data["openId"];
            $data["nickname"]    = $data["nickName"];
            $data["headimgurl"]  = $data["avatarUrl"];
            $data["session_key"] = $sessionKey;
            $data["unionid"]     = isset($data["unionId"]) ? $data["unionId"] : ((isset($data["unionid"])) ? $data["unionid"] : "");

            $returnData = $this->userHandle($data, $stands_mall_id);

            \Yii::warning("userData resultData=".json_encode($returnData));
            $setting = \Yii::$app->mall->getMallSetting(['close_auth_bind']);

            //是否开启全网通
            $phoneConfig = AppConfigLogic::getPhoneConfig();
            $close_auth_bind = 1;
            if(!empty($phoneConfig["all_network_enable"])){
                $close_auth_bind = 0;
                //是否关闭小程序绑定手机号弹框
                if(isset($setting["close_auth_bind"]) && !empty($setting["close_auth_bind"])){
                    $close_auth_bind = 1;
                }
            }

            $returnData["close_auth_bind"] = $close_auth_bind;
            if(empty($returnData)){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"授权失败");
            }
            //将获得的session_key存入缓存
            $userCache = new UserCache();
            $userCache->setValue("_authorized_session_key_".\Yii::$app->user->id,$sessionKey);
            //将获得的数据存入缓存，key为openid加密字符串
            $randStr = str_random(6);
            $openid = md5($data["openid"].$randStr);
            \Yii::$app->cache->set($openid,$data);
            $returnData["key"] = $openid;
            /*if(!empty($parent_user_id)){
                $user_data = (new UserMode()) -> getOneUserParent($returnData['access_token']);
                if(!$user_data['parent_id'] || ($user_data['parent_id'] == 9)){
                    try{
                        (new UserMode()) -> updateUsers(['parent_id' => $parent_user_id],$user_data['id']);
                    }catch (\Exception $e){

                    }
                }
            }*/
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$returnData);
        }catch (\Exception $ex){

        }
    }

    /**
     * 授权手机号
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     */
    public function authorizedMobilePhone(){
        try{
            $wechatCommon = new WechatCommon();
            $wechatCommon->iv = $this->iv;
            $wechatCommon->encryptedData = $this->encryptedData;
            $result = $wechatCommon->getAuthorizedMobilePhone();
            if(!empty($result)){
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功');
            }
            return $this->returnApiResultData(ApiCode::CODE_FAIL,'请求失败');
        }catch (\Exception $ex){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"参数有误(".$ex->getMessage().")");
        }
    }

    /**
     * 检测用户是否授权的处理方法
     * @param $userInfo
     * @return array
     * @throws \yii\base\Exception
     */
    private function userHandle($userInfo,$stands_mall_id){
        $userResult = UserLogic::checkIsAuthorized($userInfo, $this->user_id, $stands_mall_id);

        \Yii::warning("userHandle 是否授权 userResult:".var_export($userResult,true));
        if(empty($userResult)){
            /** @var User $userResult */
            $userResult = UserLogic::userRegister($userInfo,[],0,$stands_mall_id);
            if($userResult === false){
                return [];
            }
        }
        $mobile = isset($userResult->mobile) ? $userResult->mobile : "";
        \Yii::$app->user->login($userResult);
        $returnData = ['access_token' => $userResult->access_token,'mobile' => $mobile];
        return $returnData;
    }

    /**
     * 微信授权注册
     * @Author: vita
     * @Date: 2020-12-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    private function registerx($userInfo)
    {
        $access_token = \Yii::$app->security->generateRandomString();
        $trans = \Yii::$app->db->beginTransaction();
        try {
            $openid = isset($userInfo["openid"]) ? $userInfo["openid"] : "";
            $unionid = isset($userInfo["unionid"]) ? $userInfo["unionid"] : "";
            $platform = \Yii::$app->appPlatform;
            $params = ["mall_id"=>\Yii::$app->mall->id,"is_delete" => User::IS_DELETE_NO,"platform" => $platform];
            if($unionid){
                $params["unionid"] = $unionid;
            }else{
                $params["openid"] = $openid;
            }
            $existUser = UserInfo::getOneUserInfo($params);

            if(!empty($existUser)){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'已被注册');
            }

            $user = new User();
            $user->username = 'wechat_user';
            $user->mall_id = \Yii::$app->mall->id;
            $user->access_token = $access_token;
            $user->auth_key = $access_token;
            $user->nickname = isset($userInfo["nickname"]) ? $userInfo["nickname"] : "";
            $user->password = \Yii::$app->getSecurity()->generatePasswordHash(uniqid());
            $user->avatar_url = $userInfo['headimgurl'];
            $user->last_login_at = time();
            $user->login_ip = get_client_ip();
            if (!$user->save()) {
                $messages = $this->responseErrorInfo($user);
                throw new \Exception($messages["msg"]);
            }
            $userInfoModel = new UserInfo();
            $userInfoModel->mall_id = \Yii::$app->mall->id;
            $userInfoModel->mch_id = 0;
            $userInfoModel->user_id = $user->id;
            $userInfoModel->unionid = $unionid;
            $userInfoModel->openid = $openid;
            $userInfoModel->platform_data = json_encode($userInfo);
            $userInfoModel->platform = \Yii::$app->appPlatform;
            if (!$userInfoModel->save()) {
                $messages = $this->responseErrorInfo($userInfoModel);
                throw new \Exception($messages["msg"]);
            }
            \Yii::$app->user->login($user);
            $trans->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'注册成功',['access_token' => $access_token]);
        } catch (\Exception $e) {
            $trans->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}

