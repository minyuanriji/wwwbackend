<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商城设置
 * Author: xuyaoxiang
 * Date: 2020/10/12
 * Time: 10:44
 */

namespace app\forms\mall\setting;

use app\models\BaseModel;
use app\services\MallSetting\MallSettingService;

class MallSettingForm extends BaseModel
{
    public $key;
    public $value;
    public $name = null;
    public $setting_desc = null;

    public function rules()
    {
        return [
            [['key'], 'required'],
            [['value'], 'required', 'on' => 'store'],
            [['key'], 'string'],
            [['value', 'name', 'setting_desc'], 'safe'],
        ];
    }

    /**
     * 获取商城设置
     * @return array
     */
    public function getValueByKeyApiData()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($this));
        }

        $MallSettingService = new MallSettingService(\Yii::$app->mall->id);
        return $MallSettingService->getValueByKeyApiData($this->key);
    }

    /**
     * 添加数据
     * @return array
     */
    public function store()
    {
        $this->scenario = 'store';

        if (!$this->validate()) {
            return $this->returnApiResultData(99, $this->responseErrorMsg($this));
        }

        $MallSettingService = new MallSettingService(\Yii::$app->mall->id);
        return $MallSettingService->store($this->key, $this->value, $this->name, $this->setting_desc);
    }
}
