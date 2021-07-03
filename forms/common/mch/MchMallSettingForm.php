<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商户商城设置
 * Author: zal
 * Date: 2020-04-18
 * Time: 14:50
 */

namespace app\forms\common\mch;

use app\models\BaseModel;
use app\plugins\mch\models\MchMallSetting;

class MchMallSettingForm extends BaseModel
{
    public function search()
    {
        $mchMallSetting = MchMallSetting::findOne(['mch_id' => \Yii::$app->admin->identity->mch_id]);
        return $mchMallSetting;
    }
}
