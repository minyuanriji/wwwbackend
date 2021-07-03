<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 退款订单
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderRefund;

class OrderRefundForm extends BaseModel
{
    public $refund_order_id;

    public function rules()
    {
        return [
            [['refund_order_id'], 'integer']
        ];
    }

    public function shouHuo()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'status' => 2,
                'type' => OrderRefund::TYPE_REFUND_RETURN,
                'is_send' => 1,
                'id' => $this->refund_order_id
            ])->one();

            if (!$orderRefund) {
                throw new \Exception('售后订单不存在');
            }

            $orderRefund->is_confirm = 1;
            $orderRefund->confirm_at = time();
            $res = $orderRefund->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($orderRefund));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '确认收货成功'
            ];

        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}