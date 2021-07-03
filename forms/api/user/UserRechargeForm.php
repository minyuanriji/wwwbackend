<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户充值
 * Author: zal
 * Date: 2020-06-03
 * Time: 11:50
 */

namespace app\forms\api\user;

use app\core\ApiCode;
use app\core\payment\Payment;
use app\core\payment\PaymentOrder;
use app\forms\api\recharge\RechargePayNotify;
use app\helpers\SerializeHelper;
use app\helpers\sms\Sms;
use app\logic\CommonLogic;
use app\logic\OptionLogic;
use app\logic\UserLogic;
use app\models\BaseModel;
use app\models\Option;
use app\models\Order;
use app\models\Recharge;
use app\models\RechargeOrders;
use app\models\User;
use app\models\UserInfo;
use yii\db\Exception;

class UserRechargeForm extends BaseModel
{
    public $pay_price;
    public $give_money;
    public $id;

    public function rules()
    {
        return [
            [['pay_price', 'give_money'], 'required'],
            [['pay_price', 'give_money'], 'double'],
            [['id'], 'integer'],
        ];
    }

    /**
     * 获取充值配置
     * @return array|\ArrayObject|mixed
     */
    public function getRechargeSetting(){
        $res = OptionLogic::getRechargeSetting();
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"",$res);
    }

    /**
     * 充值
     * @return array|int
     * @throws \Exception
     */
    public function recharge()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $order = new RechargeOrders();
            $order->mall_id = \Yii::$app->mall->id;
            $order->order_no = Order::getOrderNo('RE');
            $order->user_id = \Yii::$app->user->id;
            if ($this->id) {
                $recharge = Recharge::findOne(['id' => $this->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
                if (!$recharge) {
                    throw new \Exception('充值错误');
                }
                $order->pay_price = $recharge->pay_price;
                $order->give_money = $recharge->give_money;
                $order->give_score = $recharge->give_score;
            } else {
                $order->pay_price = $this->pay_price;
                $order->give_money = $this->give_money;
                $order->give_score = 0;
            }
            $order->pay_type = RechargeOrders::PAY_TYPE_WECHAT;
            $res = $order->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($order));
            }

            $payOrder = new PaymentOrder([
                'title' => '余额充值',
                'amount' => floatval($this->pay_price),
                'orderNo' => $order->order_no,
                'notifyClass' => UserRechargePayNotify::class,
                'supportPayTypes' => [ //选填，支持的支付方式，若不填将支持所有支付方式。
                    \app\core\payment\Payment::PAY_TYPE_WECHAT,
                ],
            ]);
            /** @var Payment $payment */
            $payment = \Yii::$app->payment;
            $id = $payment->createOrder($payOrder);
            return $id;
        } catch (\Exception $e) {
            throw new \Exception("File:".$e->getFile().";Line:".$e->getLine().";message:".$e->getMessage());
            //return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 获取充值赠送金额
     * @param $payPrice
     * @return int
     */
    public static function getRechargeGiveMoney($payPrice){
        $res = OptionLogic::getRechargeSetting();
        $list = isset($res["list"]) ? $res["list"] : [];
        $giveMoney = 0;
        if(!empty($list)){
            foreach ($list as $value){
                if($payPrice == $value["recharge_money"]){
                    $giveMoney = $value["give_money"];
                    break;
                }
            }
        }
        return $giveMoney;
    }
}
