<?php

namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinUserAliBind;
use app\plugins\taolijin\models\TaolijinUserAuth;
use lin010\taolijin\Ali;

class AuthBindAliSpecialIdForm extends BaseModel{

    public $ali_id;

    public function rules(){
        return [
            [['ali_id'], 'required']
        ];
    }

    public function bind(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                die("联盟[ID:{$this->ali_id}]不存在");
            }

            $userAuth = TaolijinUserAuth::findOne([
                "ali_id"  => $this->ali_id,
                "user_id" => \Yii::$app->user->id
            ]);
            if(!$userAuth || $userAuth->access_token_expire_at < time()){
                throw new \Exception("授权信息不存在或已过期");
            }

            $acc = AliAccForm::getByModel($aliModel);
            $inviteCode = $acc->getAliInviteCode();
            if(empty($inviteCode)){
                throw new \Exception("联盟邀请码未生成！请联系客服进行处理");
            }

            $bindData = TaolijinUserAliBind::findOne([
                "ali_id"      => $aliModel->id,
                "user_id"     => \Yii::$app->user->id,
                "invite_code" => $inviteCode
            ]);
            if(!$bindData){
                $ali = new Ali($acc->app_key, $acc->secret_key);
                $res = $ali->publisher->save($userAuth->access_token, [
                    "inviter_code" => $inviteCode,
                    "info_type"    => "2"
                ]);
                if(!empty($res->code)){
                    throw new \Exception($res->msg);
                }
                $specialId = $res->getSpecialId();
                if(!$specialId){
                    throw new \Exception("special id获取失败");
                }

                $bindData = new TaolijinUserAliBind([
                    "mall_id"     => $aliModel->mall_id,
                    "ali_id"      => $aliModel->id,
                    "user_id"     => \Yii::$app->user->id,
                    "invite_code" => $inviteCode,
                    "special_id"  => $specialId,
                    "created_at"  => time(),
                    "updated_at"  => time()
                ]);
                if(!$bindData->save()){
                    throw new \Exception($this->responseErrorMsg($bindData));
                }
            }

            return [
                "code" => ApiCode::CODE_SUCCESS,
                "msg"  => '绑定成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}