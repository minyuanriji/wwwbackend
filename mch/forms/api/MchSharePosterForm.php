<?php
namespace app\mch\forms\api;

use app\forms\api\poster\SharePosterForm;

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
        return $option;
    }

    public function getSharePoster(){
        return parent::get($this->route);
    }
}