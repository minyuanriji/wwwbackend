<?php
namespace app\plugins\hotel\libs\bestwehotel;


use app\models\Option;

class Config{

    private static $settingArray = null;

    public static function getAppId(){
        static::getSettingArray();
        $appId = "";
        if(static::$settingArray && isset(static::$settingArray['bestwehotel']['app_id'])){
            $appId = static::$settingArray['bestwehotel']['app_id'];
        }
        return $appId;
    }

    public static function getKey(){
        static::getSettingArray();
        $key = "";
        if(static::$settingArray && isset(static::$settingArray['bestwehotel']['key'])){
            $key = static::$settingArray['bestwehotel']['key'];
        }
        return $key;
    }

    private static function getSettingArray(){
        if(static::$settingArray == null){
            $option = Option::findOne([
                "mall_id" => \Yii::$app->mall->id,
                "group"   => "hotel",
                "name"    => "platforms_setting"
            ]);
            if($option){
                static::$settingArray = @json_decode($option->value, true);
            }
        }
    }
}