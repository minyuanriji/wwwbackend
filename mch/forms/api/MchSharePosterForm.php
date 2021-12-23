<?php
namespace app\mch\forms\api;

use app\forms\api\poster\SharePosterForm;
use app\models\User;

class MchSharePosterForm extends SharePosterForm {

    public $route;
    public $name;
    public $mch_id;
    public $store_id;

    public function rules(){
        return [
            [['route'], 'required'],
            [['route', 'name'], 'string'],
            [['mch_id', 'store_id'], 'integer']
        ];
    }

    protected function optionDiff($option, $default): array{
        $option = parent::optionDiff($option, $default);
        $option['name']['text'] = $this->name;
        $option['qr_code']['size'] = 300;
        $option['qr_code']['top'] -= 0;
        $option['name']['top']    -= 0;
        $option['head']['top']    -= 25;
        return $option;
    }

    public function getSharePoster(){
        return parent::get($this->route, 0, $this->store_id);
    }

    protected function h5Path(){
        $path = "/h5/#/" . $this->route . "?mall_id=" . \Yii::$app->mall->id."&pid=" . \Yii::$app->user->id . "&source=".User::SOURCE_SHARE_POSTER . "&mch_id=" . $this->mch_id;
        return $path;
    }

    protected function h5Dir(){
        $dir = 'mch-share/' . md5(strtolower($this->route)) . "_" . \Yii::$app->mall->id."_".\Yii::$app->user->id. '.jpg';
        return $dir;
    }
}