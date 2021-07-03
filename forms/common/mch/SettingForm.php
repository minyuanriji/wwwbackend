<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 设置
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:50
 */

namespace app\forms\common\mch;


use app\forms\common\version\Compatible;
use app\models\BaseModel;
use app\models\Mall;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchSetting;

/**
 * @property Mall $mall
 */
class SettingForm extends BaseModel
{
    public $mch_id;

    public function rules()
    {
        return [
            [['mch_id'], 'integer']
        ];
    }

    public function search()
    {
        $mchId = $this->mch_id ?: \Yii::$app->admin->identity->mch_id;
        $setting = MchSetting::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'mch_id' => $mchId,
        ])->one();

        $mch = Mch::findOne($mchId);

        if (!$setting && $mch) {
            $setting = $this->getDefault();
        }
        $setting['send_type'] = Compatible::getInstance()->sendType($setting['send_type']);

        return $setting ?: [];
    }

    public function getDefault()
    {
        return [
            'is_distribution' => 0,
            'is_sms' => 0,
            'is_mail' => 0,
            'is_print' => 0,
            'is_territorial_limitation' => 0,
            'send_type' => ['express'],
            'is_web_service' => 0,
            'web_service_url' => '',
            'web_service_pic' => ''
        ];
    }
}
