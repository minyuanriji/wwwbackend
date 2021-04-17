<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单核销
 * Author: zal
 * Date: 2020-05-11
 * Time: 20:50
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\forms\common\order\OrderClerkCommon;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\ClerkUser;
use app\models\Mall;
use app\models\Order;
use app\models\User;

class OrderClerkForm extends BaseModel
{
    public $id;
    public $clerk_remark;
    public $clerk_code;
    public $action_type; // 1.小程序端确认收款 | 2.后台确认收款


    public function rules()
    {
        return [
            [['id', 'action_type'], 'integer'],
            [['id'], 'required'],
            [['clerk_remark', 'clerk_code'], 'string'],
        ];
    }

    /**
     * 确认支付
     */
    public function affirmPay()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $commonOrderClerk = new OrderClerkCommon();
            $commonOrderClerk->id = $this->id;
            $commonOrderClerk->action_type = $this->action_type;
            $commonOrderClerk->clerk_id = \Yii::$app->admin->id;
            $commonOrderClerk->clerk_type = 1;
            $res = $commonOrderClerk->affirmPay();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '收款成功',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'data' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function orderClerk()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $commonOrderClerk = new OrderClerkCommon();
            $commonOrderClerk->id           = $this->id;
            $commonOrderClerk->clerk_code   = $this->clerk_code;
            $commonOrderClerk->action_type  = $this->action_type;
            $commonOrderClerk->clerk_remark = $this->clerk_remark;
            $commonOrderClerk->clerk_id     = \Yii::$app->user->id;
            $commonOrderClerk->clerk_type   = 1;

            $commonOrderClerk->orderClerk();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '核销成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function qrClerkCode()
    {
        try {
            /** @var Order $order */
            $order = Order::find()->where([
                'id'        => $this->id,
                'is_delete' => 0,
                'mall_id'   => \Yii::$app->mall->id,
                'user_id'   => \Yii::$app->user->id,
                'send_type' => 1,
            ])->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($order->is_pay == 0 && $order->pay_type != 2) {
                throw new \Exception('订单未支付');
            }

            $appPlatform = \Yii::$app->appPlatform;
            if($appPlatform == User::PLATFORM_H5 || $appPlatform == User::PLATFORM_WECHAT){
                $dir = "order/offline-qrcode/" . $order->id . time() . '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                CommonLogic::createQrcode([], $this, '/h5/pages/more-shop/more-shop' . "?id=" . $order->id, $dir);
                $res = [
                    'file_path' => $imgUrl,
                ];
            }else{
                $qrCode = new QrCodeCommon();
                $res = $qrCode->getQrCode(['id' => $this->id, 'rnd' => time()], 100, 'pages/more-shop/more-shop');
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => array_merge($res, [
                    "code" => $order->offline_qrcode
                ])
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
