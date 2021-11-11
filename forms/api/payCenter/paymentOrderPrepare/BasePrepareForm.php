<?php

namespace app\forms\api\payCenter\paymentOrderPrepare;


use app\core\ApiCode;
use app\logic\AppConfigLogic;
use app\logic\OrderLogic;
use app\models\BaseModel;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\models\User;

abstract class BasePrepareForm extends BaseModel {

    /**
     * 创建前检查操作
     * @param User $user
     * @return void
     * @throws \Exception
     */
    abstract protected function checkBefore(User $user);

    /**
     * 订单组，格式如下：
     *  [
     *     'total_amount' => 200.00,
     *     'content'      => '描述内容',
     *     'notify_class' => '通知操作类',
     *     'list'         => [
     *          ['amount' => 100.00, 'title' => '标题1', 'order_no' => '订单号1'],
     *          ['amount' => 100.00, 'title' => '标题2', 'order_no' => '订单号2']
     *      ]
     *  ]
     * @param User $user
     * @return array
     */
    abstract protected function getOrderArray(User $user);

    public function prepare(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //用户信息判断
            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取到用户信息");
            }

            $this->checkBefore($user);

            $orderArray = $this->getOrderArray($user);

            //支持的支付方式
            $supportPayTypes = OrderLogic::getPaymentTypeConfig();
            $notSupportPayTypes = [];
            if(in_array(\Yii::$app->appPlatform, [User::PLATFORM_H5, User::PLATFORM_APP]) ){
                $notSupportPayTypes[] = "wechat";
            }elseif(in_array(\Yii::$app->appPlatform, [User::PLATFORM_WECHAT, User::PLATFORM_MP_WX])){
                $notSupportPayTypes[] = "alipay";
            }else{
                $notSupportPayTypes[] = "wechat";
                $notSupportPayTypes[] = "alipay";
            }
            $supportPayTypes = array_diff($supportPayTypes, $notSupportPayTypes);


            $unionOrderNo = 'JX' . mb_substr(md5(uniqid()), 2);
            $paymentOrderUnion = new PaymentOrderUnion();
            $paymentOrderUnion->is_pay            = 0;
            $paymentOrderUnion->mall_id           = \Yii::$app->mall->id;
            $paymentOrderUnion->user_id           = \Yii::$app->user->id;
            $paymentOrderUnion->order_no          = $unionOrderNo;
            $paymentOrderUnion->amount            = (float)$orderArray['total_amount'];
            $paymentOrderUnion->title             = $orderArray['content'];
            $paymentOrderUnion->support_pay_types = $paymentOrderUnion->encodeSupportPayTypes($supportPayTypes);
            if (!$paymentOrderUnion->save()) {
                throw new \Exception($this->responseErrorMsg($paymentOrderUnion));
            }

            foreach($orderArray['list'] as $item){
                $paymentOrder = PaymentOrder::findOne([
                    "order_no"     => $item['order_no'],
                    "notify_class" => $orderArray['notify_class']
                ]);
                if(!$paymentOrder){
                    $paymentOrder = new PaymentOrder([
                        'order_no'     => $item['order_no'],
                        'amount'       => (float)$item['amount'],
                        'is_pay'       => 0,
                        'pay_type'     => 0,
                        'title'        => $item['title'],
                        'created_at'   => time(),
                        'notify_class' => $orderArray['notify_class']
                    ]);
                }

                $paymentOrder->updated_at = time();
                $paymentOrder->payment_order_union_id = $paymentOrderUnion->id;
                if(!$paymentOrder->save()){
                    throw new \Exception($this->responseErrorMsg($paymentOrder));
                }
            }


            $supportPayTypes = OrderLogic::getPaymentTypeConfig();
            if(in_array("wechat", $supportPayTypes) && \Yii::$app->appPlatform == "h5"){
                $supportPayTypes = array_diff($supportPayTypes, ["wechat"]);
                if(!in_array("alipay", $supportPayTypes)){
                    $supportPayTypes[] = "alipay";
                }
            }

            $data = [
                'balance'         => $user->balance,
                'amount'          => $paymentOrderUnion->amount,
                'orderNo'         => $unionOrderNo,
                'supportPayTypes' => $supportPayTypes,
            ];

            $paymentConfigs = AppConfigLogic::getPaymentConfig();
            $data["pay_password_status"] = isset($paymentConfigs["pay_password_status"]) ? $paymentConfigs["pay_password_status"] : 0;
            $data["is_pay_password"]     = empty($user->transaction_password) ? 0 : 1;
            $data["union_id"]            = $paymentOrderUnion->id;
            $data["is_send"]             = 1;

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"", $data);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }
}