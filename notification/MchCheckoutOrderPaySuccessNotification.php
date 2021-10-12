<?php
namespace app\notification;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchCheckoutOrderPaySuccessNotificationWeTplJob;
use app\notification\wechat_template_message\MchCheckoutOrderPaySuccessNotificationWeTplMsg;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class MchCheckoutOrderPaySuccessNotification
{

    public static function send(MchCheckoutOrder $mchCheckoutOrder)
    {
        /*(new MchCheckoutOrderPaySuccessNotificationWeTplJob([
            "mchCheckoutOrder" => $mchCheckoutOrder
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new MchCheckoutOrderPaySuccessNotificationWeTplJob([
            "mchCheckoutOrder" => $mchCheckoutOrder
        ]));
    }

    public static function sendWechatTemplate(MchCheckoutOrder $mchCheckoutOrder)
    {
        $query = Mch::find()->alias('m')
                ->innerJoin(['s' => Store::tableName()], "s.mch_id=m.id")
                ->innerJoin(['u' => User::tableName()], "u.id=m.user_id");

        $query->andWhere(['m.id' => $mchCheckoutOrder->mch_id, 'm.review_status' => 1, 's.is_delete' => 0])
            ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        $select = ['m.transfer_rate', 'm.mall_id', 's.name', 'u.id as uid'];

        $mchResult = $query->select($select)->asArray()->one();
        if(!$mchResult) return;

        $template_id = TemConfig::IncomeArrival;
        $data = [
            'first'     => '',
            'keyword1'  => $mchCheckoutOrder->order_price * (100 - $mchResult['transfer_rate']) / 100,
            'keyword2'  => $mchResult['name'],
            'keyword3'  => date('Y-m-d H:i:s', $mchCheckoutOrder->pay_at),
            'remark'    => '您的收益已到账。',
        ];

        $userInfo = UserInfo::findOne([
            "user_id" => $mchResult['uid'],
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        (new MchCheckoutOrderPaySuccessNotificationWeTplMsg([
            "mall_id"           => $mchResult['mall_id'],
            "openid"            => $userInfo->openid,
            "data"              => $data,
            "template_id"       => $template_id,
        ]))->send();
    }
}