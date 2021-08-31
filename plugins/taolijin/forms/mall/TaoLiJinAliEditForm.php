<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;

class TaoLiJinAliEditForm extends BaseModel{

    public $id;
    public $ali_type;
    public $remark;
    public $settings_data;
    public $is_open;
    public $sort;

    public function rules(){
        return [
            [['ali_type', 'remark', 'settings_data', 'is_open', 'sort'], 'required'],
            [['id', 'is_open', 'sort'], 'integer']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $ali = TaolijinAli::findOne($this->id);
            if(!$ali){
                $ali = new TaolijinAli([
                    "mall_id" => \Yii::$app->mall->id,
                    "created_at" => time()
                ]);
            }

            $ali->ali_type      = $this->ali_type;
            $ali->remark        = $this->remark;
            $ali->settings_data = json_encode($this->settings_data);
            $ali->updated_at    = time();
            $ali->is_open       = $this->is_open;
            $ali->sort          = $this->sort;

            if(!$ali->save()){
                throw new \Exception($this->responseErrorMsg($ali));
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