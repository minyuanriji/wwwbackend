<?php
namespace app\plugins\bsh_app\forms\api;

use app\core\ApiCode;
use app\models\Apps;
use app\models\BaseModel;

class AppVersionDetailForm extends BaseModel{

    public $platform;

    public function rules(){
        return [
            [['platform'], 'required']
        ];
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $version = Apps::find()->where([
                "type"      => "bsh",
                "platform"  => $this->platform,
                "is_delete" => 0
            ])->asArray()->orderBy("version_code DESC")->one();

            if(!$version){
                throw new \Exception("无法获取版本信息");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'version_code'  => $version['version_code'],
                    'version_name'  => $version['version_name'],
                    'download_link' => $version['download_link'],
                    'date'          => date("Y-m-d", $version['updated_at'])
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