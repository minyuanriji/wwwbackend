<?php

namespace app\plugins\mch\forms\api;

use app\plugins\mch\Plugin;

class OrderSubmitForm extends \app\forms\api\order\OrderSubmitForm
{
    /**
     * @return $this
     */
    public function setEnableData()
    {
        $mallPaymentTypes = \Yii::$app->mall->getMallSettingOne('payment_type');
        if (is_array($mallPaymentTypes)) {
            foreach ($mallPaymentTypes as $key => $mallPaymentType) {
                if ($mallPaymentType == 'huodao') {
                    unset($mallPaymentTypes[$key]);
                    $mallPaymentTypes = array_values($mallPaymentTypes);
                    break;
                }
            }
        }

        return $this->setEnableIntegral(false)->setEnableCoupon(false)->setEnableMemberPrice(false)
            ->setSign((new Plugin())->getName())->setSupportPayTypes($mallPaymentTypes)->setEnableFullReduce(true);
    }
}
