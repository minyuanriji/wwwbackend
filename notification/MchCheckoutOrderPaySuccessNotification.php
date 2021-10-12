<?php
namespace app\notification;

use app\forms\efps\EfpsTransfer;
use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchCheckoutOrderPaySuccessNotificationWeTplJob;
use app\notification\wechat_template_message\MchCheckoutOrderPaySuccessNotificationWeTplMsg;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;
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

        $query->andWhere(['id' => $mchCheckoutOrder->mch_id, 'review_status' => 1])
            ->andWhere(['and', ['<>', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        $select = [];

        $query->select($select)->one();

        $store = Store::findOne(['mch_id' => $mchCheckoutOrder->mch_id, 'is_delete' => 0]);
        if(!$store) return;

        return;
        $user = User::findOne($mch->user_id);
        if(!$user) return;

        $template_id = TemConfig::IncomeArrival;
        $data = [
            'first'     => '收益到账通知：',
            'keyword1'  => $mchCheckoutOrder->order_price * (100 - $mch->transfer_rate) / 100,
            'keyword2'  => $store->name,
            'keyword3'  => date('Y-m-d H:i:s', $mchCheckoutOrder->pay_at),
            'remark'    => '您的收益已到账。',
        ];

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        (new MchCheckoutOrderPaySuccessNotificationWeTplMsg([
            "mall_id"           => $mch_cash->mall_id,
            "openid"            => $userInfo->openid,
            "data"              => $data,
            "template_id"       => $template_id,
        ]))->send();
    }
}