<?php

namespace app\plugins\mch\forms\api;

use app\core\ApiCode;
use app\forms\common\CommonAppConfig;
use app\forms\common\goods\CommonGoodsDetail;
use app\forms\common\goods\CommonGoodsList;
use app\forms\common\mch\SettingForm;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\ShareSetting;
use app\models\User;
use app\plugins\mch\models\Goods;
use app\plugins\mch\models\MchMallSetting;
use app\plugins\mch\models\MchSetting;
use app\forms\common\goods\CommonGoods;

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
