<?php
namespace app\mch\forms\api;

use app\forms\api\poster\SharePosterForm;
use app\models\User;

class MchSharePosterForm extends SharePosterForm {

    public $route;
    public $name;

    public function rules(){
        return [
            [['route'], 'required'],
            [['route', 'name'], 'string']
        ];
    }

    protected function optionDiff($option, $default): array{
        $option = parent::optionDiff($option, $default);
        $option['name']['text'] = $this->name;
        $option['qr_code']['size'] = 200;
        $option['qr_code']['top'] -= 50;
        $option['name']['top']    -= 50;
        $option['head']['top']    -= 50;
        return $option;
    }

    public function getSharePoster(){
        return parent::get($this->route);
    }

    protected function h5Path(){
        $path = "/h5/#/" . $this->route . "?mall_id=" . \Yii::$app->mall->id."&pid=" . \Yii::$app->user->id . "&source=".User::SOURCE_SHARE_POSTER;
        return $path;
    }

    protected function h5Dir(){
        $dir = 'mch-share/' . \Yii::$app->mall->id."_".\Yii::$app->user->id. '.jpg';
        return $dir;
    }
}