<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 角色设置
 * Author: zal
 * Date: 2020-04-10
 * Time: 10:12
 */

namespace app\forms\common;


use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Option;

class RoleSettingForm extends BaseModel
{
    public $mall_id;

    /**
     * 获取配置数据
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:12
     * @return array|mixed|null
     */
    public function getSettingInfo()
    {
        $settingData = OptionLogic::get(
            Option::NAME_ROLE_SETTING,
            $this->mall_id ?: \Yii::$app->mall->id,
            Option::GROUP_ADMIN
        );
        $settingData = $settingData ?: [];
        $settingData = $this->checkData($settingData, $this->getDefault());

        $arr = ['update_password_status'];
        foreach ($settingData as $key => $item) {
            if (in_array($key, $arr)) {
                $settingData[$key] = (int)$item;
            }
        }

        return $settingData;
    }

    /**
     * 已存储数据和默认数据对比，以默认数据字段为准
     * @Author: 广东七件事 zal
     * @Date: 2020-04-10
     * @Time: 10:12
     * @param $list
     * @param $default
     * @return mixed
     */
    private function checkData($list, $default)
    {
        foreach ($default as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $list[$key] = $this->checkData($list[$key], $value);
            }
        }
        return $list;
    }


    protected function getDefault()
    {
        return [
            'logo' => '',
            'copyright' => '',
            'copyright_url' => '',
            'update_password_status' => 1,
        ];
    }
}