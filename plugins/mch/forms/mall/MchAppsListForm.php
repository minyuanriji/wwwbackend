<?php
namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\models\Apps;
use app\models\BaseModel;

class MchAppsListForm extends BaseModel{

    public $page;
    public $platform;

    public function rules(){
        return [
            [['platform'], 'required'],
            [['page'], 'safe']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $query = Apps::find()->where([
            "is_delete" => 0,
            "platform"  => $this->platform,
            "type"      => "merchant"
        ])->orderBy('version_code DESC');
        $list = $query->page($pagination, 20, (int)$this->page)->asArray()->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list'       => $list,
                'pagination' => $pagination
            ]
        ];
    }
}