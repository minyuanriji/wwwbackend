<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\helpers\MobileHelper;
use app\models\BaseModel;
use app\models\User;
use app\models\UserInfo;
use app\plugins\smart_shop\models\KpiUser;

class KpiUserEditForm extends BaseModel{

    public $merchant_id;
    public $store_id;
    public $realname;
    public $mobile;

    public function rules() {
        return [
            [['merchant_id', 'store_id', 'realname', 'mobile'], 'required'],
            [['realname', 'mobile'], 'trim']
        ];
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            if(!MobileHelper::isMobile($this->mobile)){
                throw new \Exception("手机号码格式有误");
            }

            //注册本地用户
            $user = User::findOne(["mobile" => $this->mobile]);
            if(!$user){
                $user = new User();
                $user->username         = "u" . uniqid();
                $user->mobile           = $this->mobile;
                $user->mall_id          = \Yii::$app->mall->id;
                $user->access_token     = \Yii::$app->security->generateRandomString();
                $user->auth_key         = \Yii::$app->security->generateRandomString();
                $user->nickname         = $this->realname;
                $user->realname         = $this->realname;
                $user->password         = \Yii::$app->getSecurity()->generatePasswordHash(uniqid());
                $user->avatar_url       = "/";
                $user->last_login_at    = time();
                $user->login_ip         = "#";
                $user->parent_id        = GLOBAL_PARENT_ID;
                $user->second_parent_id = 0;
                $user->third_parent_id  = 0;

                if (!$user->save()) {
                    throw new \Exception(json_encode($user->getErrors()));
                }

                $userInfoModel = new UserInfo();
                $userInfoModel->mall_id       = \Yii::$app->mall->id;
                $userInfoModel->mch_id        = 0;
                $userInfoModel->user_id       = $user->id;
                $userInfoModel->unionid       = "";
                $userInfoModel->openid        = "";
                $userInfoModel->platform_data = "";
                $userInfoModel->platform      = "mp-wx";
                if (!$userInfoModel->save()) {
                    throw new \Exception(json_encode($userInfoModel->getErrors()));
                }
            }


            $uniqueData = [
                "mall_id"      => \Yii::$app->mall->id,
                "ss_mch_id"    => $this->merchant_id,
                "ss_store_id"  => $this->store_id,
                "mobile"       => $this->mobile
            ];
            $kpiUser = KpiUser::findOne($uniqueData);
            if(!$kpiUser){
                $kpiUser = new KpiUser(array_merge($uniqueData, [
                    "created_at" => time()
                ]));
            }
            $kpiUser->user_id    = $user->id;
            $kpiUser->updated_at = time();
            $kpiUser->realname   = $this->realname;
            $kpiUser->is_delete  = 0;
            if(!$kpiUser->save()){
                throw new \Exception($this->responseErrorMsg($kpiUser));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}