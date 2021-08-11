<?php


namespace app\forms\api\payCenter;


use app\component\efps\Efps;
use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsPaymentOrder;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\models\User;
use app\models\UserInfo;

abstract class EfpsWechatBasePayForm extends BaseModel{

    const PAY_API_CODE = "IF-WeChat-01";
    const NOTIFY_URI   = "/web/pay-notify/efps.php";

    public $union_id;
    public $redirect_url;
    public $stands_mall_id;

    public function rules(){
        return [
            [['union_id'], 'required'],
            [['stands_mall_id'], 'integer'],
            [['redirect_url'], 'string']
        ];
    }

    /**
     * 返回商品信息，格式如下：
     *  [
     *    "goodsId" => [商品ID],
     *    "name"    => "商品名称",
     *    "price"   => [价格（单位分）],
     *    "number"  => [数量],
     *    "amount"  => [价格（单位分）]
     *  ]
     * @param User $user
     * @param PaymentOrderUnion $paymentOrderUnion
     * @param PaymentOrder $paymentOrder
     * @return array
     */
    abstract protected function orderInfoGoods(User $user, PaymentOrderUnion $paymentOrderUnion, PaymentOrder $paymentOrder);

    /**
     * 检查能否进行支付
     * @param User $user
     * @param PaymentOrderUnion $paymentOrderUnion
     * @param PaymentOrder $paymentOrder
     * @return void
     */
    abstract protected function checkBefore(User $user, PaymentOrderUnion $paymentOrderUnion, PaymentOrder $paymentOrder);

    public function getJsapiParam(){

        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {

            $paymentOrderUnion = PaymentOrderUnion::findOne($this->union_id);

            if(!$paymentOrderUnion){
                throw new \Exception("支付记录不存在");
            }

            if($paymentOrderUnion->is_pay){
                throw new \Exception("请勿重新支付");
            }

            $paymentOrders = $paymentOrderUnion->paymentOrder;
            if(!$paymentOrders){
                throw new \Exception("支付订单记录不存在");
            }

            //获取用户信息
            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取用户信息");
            }

            //易票联需要的订单数组
            $orderInfo = [
                'Id'           => $paymentOrderUnion->id,
                "businessType" => "100001",
                "goodsList"    => []
            ];
            foreach($paymentOrders as $paymentOrder){
                $info = $this->orderInfoGoods($user, $paymentOrderUnion, $paymentOrder);
                $orderInfo['goodsList'][] = [
                    "goodsId" => (string)$info['goodsId'],
                    "name"    => $info['name'],
                    "price"   => $info['price'],
                    "number"  => (string)$info['number'],
                    "amount"  => (string)$info['amount']
                ];

                //检查能否支付
                $this->checkBefore($user, $paymentOrderUnion, $paymentOrder);
            }

            $efpsPaymentOrder = EfpsPaymentOrder::findOne([
                "payment_order_union_id" => $paymentOrderUnion->id,
                "payAPI"                 => self::PAY_API_CODE
            ]);
            if(!$efpsPaymentOrder){
                $efpsPaymentOrder = new EfpsPaymentOrder();
                $efpsPaymentOrder->payment_order_union_id = $paymentOrderUnion->id;
                $efpsPaymentOrder->payAPI                 = self::PAY_API_CODE;
            }

            $efpsPaymentOrder->orderInfo              = json_encode($orderInfo);
            $efpsPaymentOrder->customerCode           = \Yii::$app->efps->getCustomerCode();
            $efpsPaymentOrder->payAmount              = $paymentOrderUnion->amount * 100;
            $efpsPaymentOrder->payCurrency            = "CNY";
            $efpsPaymentOrder->outTradeNo             = date("YmdHis") . rand(10000, 99999);
            $efpsPaymentOrder->transactionStartTime   = date("YmdHis");
            $efpsPaymentOrder->nonceStr               = md5(uniqid());
            $efpsPaymentOrder->notifyUrl              = self::NOTIFY_URI;
            $efpsPaymentOrder->redirectUrl            = $this->redirect_url;
            $efpsPaymentOrder->update_at              = time();
            $efpsPaymentOrder->is_pay                 = 0;

            $appPlatform = \Yii::$app->appPlatform;
            if($appPlatform == User::PLATFORM_H5 || $appPlatform == User::PLATFORM_WECHAT){ //H5、公众号
                $where = [
                    "user_id"  => $user->id,
                    "platform" => $appPlatform,
                ];
            }else{ //小程序
                if(!$this->stands_mall_id){
                    throw new \Exception("商城ID不存在");
                }
                $where = [
                    "user_id"  => $user->id,
                    "platform" => $appPlatform,
                    "mall_id"  => $this->stands_mall_id,
                ];
            }

            $userInfo = UserInfo::findOne($where);
            if(!$userInfo || empty($userInfo->openid)){
                throw new \Exception("用户未授权，无法支付");
            }

            if($appPlatform == User::PLATFORM_H5 || $appPlatform == User::PLATFORM_WECHAT){ //H5、公众号
                $efpsPaymentOrder->payMethod = "35";
                $efpsPaymentOrder->appId = \Yii::$app->params['wechatConfig']['app_id'];
            }else{ //小程序
                $efpsPaymentOrder->payMethod = "1";
                $efpsPaymentOrder->appId = \Yii::$app->params['wechatMiniProgramConfig']['app_id'];
            }
            $efpsPaymentOrder->openId = $userInfo->openid;
            if(!$efpsPaymentOrder->save()){
                throw new \Exception($this->responseErrorMsg($efpsPaymentOrder));
            }

            $data = [
                "outTradeNo"   => $efpsPaymentOrder->outTradeNo,
                "customerCode" => $efpsPaymentOrder->customerCode,
                "payAmount"    => $efpsPaymentOrder->payAmount,
                "notifyUrl"    => $efpsPaymentOrder->notifyUrl,
                "redirectUrl"  => $efpsPaymentOrder->redirectUrl,
                "orderInfo"    => json_decode($efpsPaymentOrder->orderInfo, true)
            ];

            $res = \Yii::$app->efps->payWxJSAPIPayment(array_merge($data, [
                "appId"  => $efpsPaymentOrder->appId,
                "openId" => $efpsPaymentOrder->openId
            ]));

            if($res['code'] != Efps::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            $data = $res['data']['wxJsapiParam'];
            $data['wx_type'] = $appPlatform == User::PLATFORM_H5 || $appPlatform == User::PLATFORM_WECHAT ? "wechat" : "mp-wx";

            return [
                'code'  => ApiCode::CODE_SUCCESS,
                'msg'   => '请求成功',
                'data'  => $data
            ];
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}