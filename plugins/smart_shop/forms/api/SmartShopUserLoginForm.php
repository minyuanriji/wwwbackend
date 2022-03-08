<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\models\UserInfo;
use app\plugins\mch\models\Mch;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;
use function EasyWeChat\Kernel\Support\get_client_ip;

class SmartShopUserLoginForm extends BaseModel{

    public $openid;
    public $mobile;
    public $ss_store_id;
    public $ali_uid;

    public function rules(){
        return [
            [['mobile', 'ss_store_id'], 'required'],
            [['mobile', 'openid', 'ali_uid'], 'trim']
        ];
    }

    public function login(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $smartShop = new SmartShop();
            $smartAuthUser = $smartShop->findUsersByOpenid($this->openid, $this->ali_uid);
            /*if(!$smartAuthUser || $smartAuthUser['mobile'] != $this->mobile){
                throw new \Exception("用户不存在或未登录");
            }*/

            //通过智慧门店ID查找关联补商汇商户所绑定的用户ID
            $row = MerchantFzlist::find()->alias("mfl")
                ->innerJoin(["m" => Mch::tableName()], "m.id=mfl.bsh_mch_id")
                ->innerJoin(["mf" => Merchant::tableName()], "mf.bsh_mch_id=mfl.bsh_mch_id")
                ->select(["m.user_id"])
                ->where([
                    "mfl.ss_store_id" => $this->ss_store_id,
                    "mfl.is_delete"   => 0,
                    "mf.is_delete"    => 0
                ])->asArray()->one();
            if(!$row){
                throw new \Exception("该门店未设置红包兑换功能");
            }
            $ssStoreLocalUserId = $row['user_id'];

            $user = User::findOne(["mobile" => $this->mobile]);
            if(!$user){
                $user = new User();
                $user->username         = $this->mobile;
                $user->mobile           = $this->mobile;
                $user->mall_id          = \Yii::$app->mall->id;
                $user->access_token     = \Yii::$app->security->generateRandomString();
                $user->auth_key         = \Yii::$app->security->generateRandomString();
                $user->nickname         = $smartAuthUser && !empty($smartAuthUser['nickname']) ? $smartAuthUser['nickname'] : uniqid();
                $user->password         = \Yii::$app->getSecurity()->generatePasswordHash(uniqid());
                $user->avatar_url       = $smartAuthUser && !empty($smartAuthUser['avatar']) ? $smartAuthUser['avatar'] : "/";
                $user->last_login_at    = time();
                $user->login_ip         = get_client_ip();
                $user->parent_id        = $ssStoreLocalUserId;
                $user->second_parent_id = 0;
                $user->third_parent_id  = 0;
                if (!$user->save()) {
                    throw new \Exception($this->responseErrorInfo($user));
                }

                $userInfoModel = new UserInfo();
                $userInfoModel->mall_id       = \Yii::$app->mall->id;
                $userInfoModel->mch_id        = 0;
                $userInfoModel->user_id       = $user->id;
                $userInfoModel->unionid       = "";
                $userInfoModel->openid        = "";
                $userInfoModel->platform_data = "";
                $userInfoModel->platform      = \Yii::$app->appPlatform;
                if (!$userInfoModel->save()) {
                    throw new \Exception($this->responseErrorInfo($userInfoModel));
                }
            }else{
                $user->access_token     = \Yii::$app->security->generateRandomString();
                $user->auth_key         = \Yii::$app->security->generateRandomString();
            }

            if (!$user->save()) {
                throw new \Exception($this->responseErrorInfo($user));
            }

            //获取用户剩余的红包
            $remainShoppingVoucherNum = 0;
            $shoppingVoucherUserModel = ShoppingVoucherUser::findOne(["user_id" => $user->id]);
            if($shoppingVoucherUserModel){
                $remainShoppingVoucherNum = $shoppingVoucherUserModel->money;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "remain_integral_num"         => intval($user->static_score + $user->score),
                    "remain_shopping_voucher_num" => $remainShoppingVoucherNum,
                    "token"                       => $user->access_token
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}