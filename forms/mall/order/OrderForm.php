<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\forms\common\mch\MchSettingForm;

class OrderForm extends BaseOrderForm
{
    protected function extraConfirmWhere()
    {
        if (\Yii::$app->admin->identity->mch_id > 0) {
            $mchSettingForm = new MchSettingForm();
            $mchSetting = $mchSettingForm->search();

            if (!$mchSetting['is_confirm_order']) {
                throw new \Exception('商户无权限确认收货');
            }
        }
    }
}
