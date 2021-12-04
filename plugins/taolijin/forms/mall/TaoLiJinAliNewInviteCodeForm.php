<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinAliInvitecode;
use lin010\taolijin\Ali;

class TaoLiJinAliNewInviteCodeForm extends BaseModel{

    public $ali_id;
    public $access_token;
    public $open_uid;

    public function rules(){
        return [
            [['ali_id', 'access_token', 'open_uid'], 'required']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟数据不存在");
            }

            $model = TaolijinAliInvitecode::findOne([
                "ali_id"   => $aliModel->id,
                "open_uid" => $this->open_uid
            ]);
            if(!$model){
                $acc = AliAccForm::getByModel($aliModel);
                $ali = new Ali($acc->app_key, $acc->secret_key);
                $res = $ali->invitecode->getInviteCode($this->access_token, [
                    "relation_app" => "common", //渠道推广的物料类型
                    "code_type"    => "3" //邀请码类型，1 - 渠道邀请，2 - 渠道裂变，3 -会员邀请
                ]);
                if(!empty($res->code)){
                    throw new \Exception($res->msg);
                }

                $inviteCode = $res->getCode();
                if(empty($inviteCode)){
                    throw new \Exception("邀请码获取失败");
                }

                if(!$model){
                    $model = new TaolijinAliInvitecode([
                        "mall_id"    => $aliModel->mall_id,
                        "ali_id"     => $aliModel->id,
                        "open_uid"   => $this->open_uid,
                        "created_at" => time()
                    ]);
                }
                $model->code = $inviteCode;
            }else{
                $inviteCode = $model->code;
            }

            $model->is_delete  = 0;
            $model->updated_at = time();
            if(!$model->save()){
                throw new \Exception($this->responseErrorMsg($model));
            }

            $aliData = $aliModel->getAttributes();
            $aliData['is_open'] = (int)$aliData['is_open'];
            $aliData['settings_data'] = !empty($aliData['settings_data']) ? json_decode($aliData['settings_data'], true) : [];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'ali_data'    => $aliData,
                    'invite_code' => $inviteCode
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