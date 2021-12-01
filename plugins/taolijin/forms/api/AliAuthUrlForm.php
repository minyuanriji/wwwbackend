<?php

namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;

class AliAuthUrlForm extends BaseModel{

    public $ali_id;

    public function rules(){
        return [
            [['ali_id'], 'required']
        ];
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne([
                "id"       => $this->ali_id,
                "ali_type" => "ali"
            ]);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟[ID:{$this->ali_id}]不存在");
            }

            $setting = @json_decode($aliModel->settings_data, true);

            $hostInfo = \Yii::$app->getRequest()->getHostInfo();
            $hostInfo = "https://dev.mingyuanriji.cn";
            $redirectUri = "{$hostInfo}/h5/#/ali/taolijin/auth/auth";

            $authUrl  = "https://oauth.m.taobao.com/authorize?response_type=code&client_id=" . $setting['app_key'] . "&redirect_uri={$redirectUri}&state=".uniqid()."&view=wap";

            return [
                "code" => ApiCode::CODE_SUCCESS,
                "data" => [
                    'auth_url' => $authUrl
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