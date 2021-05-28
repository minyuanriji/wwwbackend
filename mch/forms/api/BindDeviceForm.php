<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;

class BindDeviceForm extends BaseModel{

    public function bind(){
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg'  => '操作成功'
        ];
    }

}