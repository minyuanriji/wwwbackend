<?php

namespace app\plugins\taobao\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taobao\models\TaobaoAccount;

class TaobaoSettingEditForm extends BaseModel{

    public $data;

    public function rules() {
        return [
            [['data'], 'safe']
        ];
    }

    /**
     * 获取所有配置
     * @return array
     */
    public function settings(){
        $account = TaobaoAccount::findOne(1);
        if($account){
            $settings = [
                'app_key'     => $account->app_key,
                'app_secret'  => $account->app_secret,
                'adzone_id'   => $account->adzone_id,
                'invite_code' => $account->invite_code,
                'special_id'  => $account->special_id,
            ];
        }else{
            $settings = [
                'app_key'     => '',
                'app_secret'  => '',
                'adzone_id'   => '',
                'invite_code' => '',
                'special_id'  => ''
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'setting' => $settings
            ]
        ];;
    }

    /**
     * 保存配置
     * @return array
     */
    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $account = TaobaoAccount::findOne(1);
            if(!$account){
                $account = new TaobaoAccount([
                    "mall_id"    => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }
            $account->updated_at  = time();
            $account->app_secret  = $this->data['app_secret'];
            $account->adzone_id   = $this->data['adzone_id'];
            $account->special_id  = $this->data['special_id'];
            $account->invite_code = $this->data['invite_code'];
            if(!$account->save()){
                throw new \Exception($this->responseErrorMsg($account));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}