<?php

namespace app\plugins\mch\forms\api;

use app\core\ApiCode;
use app\helpers\CityHelper;
use app\models\BaseModel;

class MchGetCityLowerForm extends BaseModel
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function getRegion()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $district = CityHelper::getLower($this->id);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', $district);
    }

}
