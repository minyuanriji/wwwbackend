<?php

namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinUserAuth;
use lin010\taolijin\Ali;

class AuthGetInfoForm extends BaseModel{

    public $ali_id;

    public function rules(){
        return [
            [['ali_id'], 'required']
        ];
    }
    public function getInfo(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                die("联盟[ID:{$this->ali_id}]不存在");
            }

            $isNeedAuth = false;
            $userAuth = TaolijinUserAuth::findOne([
                "ali_id"  => $this->ali_id,
                "user_id" => \Yii::$app->user->id
            ]);
            if(!$userAuth){
                $isNeedAuth = true;
            }elseif($userAuth->access_token_expire_at < time()){ //过期了
                if($userAuth->refresh_token_expire_at > time()){
                    $acc = AliAccForm::getByModel($aliModel);
                    $ali = new Ali($acc->app_key, $acc->secret_key);

                    $res = $ali->auth->refreshToken([
                        "refresh_token" => $userAuth->refresh_token
                    ]);

                    if(!empty($res->code)){
                        throw new \Exception($res->msg);
                    }

                    $tokenData = $res->getTokenData();

                    $userAuth->updated_at              = time();
                    $userAuth->refresh_token_expire_at = intval($tokenData['refresh_token_valid_time']/1000) - 3600 * 24;
                    $userAuth->refresh_token           = $tokenData['refresh_token'];
                    $userAuth->access_token_expire_at  = intval($tokenData['expire_time']/1000);
                    $userAuth->access_token            = $tokenData['access_token'];
                    if(!$userAuth->save()){
                        throw new \Exception($this->responseErrorMsg($userAuth));
                    }
                }else{
                    $isNeedAuth = true;
                }
            }

            return [
                "code" => ApiCode::CODE_SUCCESS,
                "data" => [
                    "need_auth" => $isNeedAuth ? 1 : 0
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