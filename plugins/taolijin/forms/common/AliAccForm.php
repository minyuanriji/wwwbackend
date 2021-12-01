<?php

namespace app\plugins\taolijin\forms\common;

use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;

class AliAccForm extends BaseModel{

    public $id;
    public $settings_data = [];

    public function rules() {
        return [
            [["id", "settings_data"], "required"]
        ];
    }

    public function __get($name){
        return isset($this->settings_data[$name]) ? $this->settings_data[$name] : null;
    }

    /**
     * @param string $type
     * @return AliAccForm
     */
    public static function getByModel(TaolijinAli $aliModel){
        $data['id'] = $aliModel->id;
        $data['settings_data'] = json_decode($aliModel->settings_data, true);
        return new AliAccForm($data);
    }

    /**
     * 获取一个可用的账号
     * @param string $type
     * @return AliAccForm
     */
    public static function get($type){

        $data = TaolijinAli::find()->where([
            "is_open"   => 1,
            "is_delete" => 0,
            "ali_type"  => $type
        ])->orderBy("sort DESC")->asArray()->select(["id", "settings_data"])->one();

        if(!$data){
            $data = [
                'id' => 0,
                'settings_data' => []
            ];
        }else{
            $data['id'] = $data['id'];
            $data['settings_data'] = json_decode($data['settings_data'], true);
        }

        return new AliAccForm($data);
    }

}