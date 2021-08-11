<?php
namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\models\Apps;
use app\models\BaseModel;

class MchAppsEditForm extends BaseModel{

    public $id;
    public $platform;
    public $version_code;
    public $version_name;
    public $download_link;
    public $content;

    public function rules() {
        return [
            [['version_code', 'version_name', 'download_link', 'platform'], 'required'],
            [['content', 'id'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $model = Apps::findOne($this->id);
            if(!$model){
                $model = new Apps([
                    "mall_id"    => \Yii::$app->mall->id,
                    "platform"   => $this->platform,
                    "type"       => "merchant",
                    "created_at" => time()
                ]);
            }

            $model->version_code  = $this->version_code;
            $model->version_name  = $this->version_name;
            $model->download_link = $this->download_link;
            $model->updated_at    = time();
            $model->content       = $this->content;

            if(!$model->save()){
                throw new \Exception($this->responseErrorMsg($model));
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