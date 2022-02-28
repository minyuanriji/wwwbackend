<?php
namespace app\mch\handlers;


use app\core\ApiCode;
use app\forms\common\WebSocketRequestForm;
use app\helpers\tencent_cloud\TencentCloudAudioHelper;
use app\logic\RelationLogic;
use app\mch\events\CheckoutOrderPaidEvent;
use app\mch\forms\order\CheckoutOrderDeductIntegralForm;
use app\models\Mall;
use app\forms\efps\distribute\EfpsDistributeForm;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAdminUser;
use app\plugins\mch\models\MchMessage;

class CheckoutOrderPaidHandler {

    public static function handle(CheckoutOrderPaidEvent $event){

        $checkoutOrder = $event->checkoutOrder;
        $amount        = $event->amount;

        $mall = Mall::findOne([
            'id'         => $checkoutOrder->mall_id,
            'is_delete'  => 0,
            'is_recycle' => 0,
        ]);

        \Yii::$app->setMallId($mall->id);
        \Yii::$app->setMall($mall);

        $status = 0;

        if(!$checkoutOrder->is_pay){

            $t = \Yii::$app->db->beginTransaction();
            try {
                $checkoutOrder->pay_price  = $amount;
                $checkoutOrder->is_pay     = 1;
                $checkoutOrder->pay_at     = time();
                $checkoutOrder->updated_at = time();
                if(!$checkoutOrder->save()){
                    throw new \Exception('保存结账单失败');
                }

                //获取门店名称
                $store_name = Store::find()->where([
                    'id'            => $checkoutOrder->store_id,
                    'is_delete'     => 0,
                ])->one();

                //获取商户信息
                $mch = Mch::findOne($store_name->mch_id);

                //金豆券抵扣
                if($checkoutOrder->integral_deduction_price > 0){
                    $deductIntegralForm = new CheckoutOrderDeductIntegralForm([
                        "user_id"           => $checkoutOrder->pay_user_id,
                        "deduction_price"   => $checkoutOrder->integral_deduction_price,
                        "source_id"         => $checkoutOrder->id,
                        "desc"              => "商家" . $store_name->name . "结账单(" . $checkoutOrder->id . ")付款",
                        "source_table"      => "plugin_mch_checkout_order"
                    ]);
                    if(!$deductIntegralForm->save()){
                        throw new \Exception(CheckoutOrderDeductIntegralForm::$errorMsg);
                    }
                }

                //分账业务
                $res = EfpsDistributeForm::checkoutOrder($checkoutOrder);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }

                $t->commit();

                $status = 1;

                static::voiceNotify($mch, "补商汇到账" . $checkoutOrder->order_price . "元");

            }catch (\Exception $e){
                $t->rollBack();
                throw new \Exception($e->getMessage());
            }
        }

        //如果支付用户没有上级，设置用户的上级为商家所绑定的小程序用户
        if($status){
            $payUser = User::findOne($checkoutOrder->pay_user_id);
            try {
                RelationLogic::bindParent($payUser, $mch->user_id);
            }catch (\Exception $e){

            }
        }
    }

    /**
     * 通知商户支付成功了
     * @param $mch
     * @param $text
     */
    public static function voiceNotify(Mch $mch, $text){

        $rows = MchAdminUser::find()->andWhere([
            "AND",
            ["mch_id" => $mch->id],
            "token_expired_at > '".time()."'"
        ])->asArray()->select(["id"])->all();
        foreach($rows as $row){
            $mchMessage = new MchMessage([
                "mall_id"       => $mch->mall_id,
                "mch_id"        => $mch->id,
                "type"          => "paid_notify_voice",
                "content"       => $text,
                "status"        => 0,
                "created_at"    => time(),
                "updated_at"    => time(),
                "try_count"     => 0,
                "admin_user_id" => $row['id']
            ]);
            $mchMessage->save();
        }

        /*$base64Data = TencentCloudAudioHelper::request($text);

        if(!empty($base64Data)){
            $data = [
                "text"       => $text,
                "base64Data" => $base64Data,
                "url"        => ""
            ];

            WebSocketRequestForm::add(new WebSocketRequestForm([
                'action'        => 'MchPaidNotify',
                'notify_mobile' => $token,
                'notify_data'   => "PAID:" . json_encode($data)
            ]));
        }*/
    }

}