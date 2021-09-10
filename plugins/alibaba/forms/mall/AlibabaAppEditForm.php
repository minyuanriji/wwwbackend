<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;

class AlibabaAppEditForm extends BaseModel{

    public $id;
    public $name;
    public $type;
    public $app_key;
    public $secret;

    public function rules(){
        return [
            [['name', 'type', 'app_key', 'secret'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $app = AlibabaApp::findOne($this->id);
            if(!$app){
                $app = new AlibabaApp([
                    "mall_id"    => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }

            $app->name    = $this->name;
            $app->type    = $this->type;
            $app->app_key = $this->app_key;
            $app->secret  = $this->secret;

            if(!$app->save()){
                throw new \Exception($this->responseErrorMsg($app));
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