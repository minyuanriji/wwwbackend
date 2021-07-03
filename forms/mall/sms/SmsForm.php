<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 短信
 * Author: zal
 * Date: 2020-04-23
 * Time: 16:36
 */

namespace app\forms\mall\sms;

use app\core\ApiCode;
use app\forms\common\SmsCommon;
use app\logic\AppConfigLogic;
use app\models\BaseModel;

class SmsForm extends BaseModel
{
    public function getDetail()
    {
        $option = AppConfigLogic::getSmsConfig();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'detail' => $option,
                'setting' => SmsCommon::getCommon()->getSetting()
            ]
        ];
    }

    /**
     * 获取默认短信内容配置
     * @return array
     */
    public function getDefault()
    {
        $setting = SmsCommon::getCommon()->getSetting();
        $result = [
            'status' => '0',
            'platform' => 'aliyun',// 短信默认支持阿里云
            'mobile_list' => [],
            'access_key_id' => '',
            'access_key_secret' => '',
            'template_name' => '',
        ];
        foreach ($setting as $index => $item) {
            $newItem = [
                'template_id' => ''
            ];
            foreach ($item['variable'] as $value) {
                $newItem[$value['key']] = '';
            }
            $result[$index] = $newItem;
        }
        return $result;
    }
}
