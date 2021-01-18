<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单售后换货发货
 * Author: zal
 * Date: 2020-05-13
 * Time: 14:55
 */

namespace app\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderRefund;

class OrderRefundSendForm extends BaseModel
{
    public $id;
    public $express;
    public $express_no;
    public $customer_name;//京东物流特殊要求字段，商家编码


    public function rules()
    {
        return [
            [['id', 'express', 'express_no'], 'required'],
            [['id'], 'integer'],
            [['express', 'express_no', 'customer_name'], 'string']
        ];
    }

    public function send()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,"缺少参数");
        }
        try {
            if (substr_count($this->express, '京东') && empty($this->customer_name)) {
                throw new \Exception('京东物流必须填写京东商家编码');
            }
            /** @var OrderRefund $orderRefund */
            $orderRefund = OrderRefund::find()->where([
                'id' => $this->id,
                'user_id' => \Yii::$app->user->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0
            ])->one();

            if (!$orderRefund) {
                throw new \Exception('订单不存在');
            }

            if ($orderRefund->is_send) {
                throw new \Exception('订单已发货,无需重复操作');
            }

            $orderRefund->customer_name = $this->customer_name;
            $orderRefund->express       = $this->express;
            $orderRefund->express_no    = $this->express_no;
            $orderRefund->is_send       = 1;
            $orderRefund->send_at       = time();
            $orderRefund->is_confirm    = 0;
            $orderRefund->type          = OrderRefund::TYPE_REFUND_RETURN;
            $orderRefund->save();

            if (!$orderRefund) {
                throw new \Exception($this->responseErrorMsg($orderRefund));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '发货成功'
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
