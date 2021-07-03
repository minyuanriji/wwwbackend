<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单打印
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:11
 */

namespace app\forms\mall\order;

use app\core\ApiCode;
use app\forms\common\order\OrderCommon;
use app\forms\common\prints\Exceptions\PrintException;
use app\forms\common\prints\PrintOrder;
use app\models\BaseModel;
use app\models\Order;

class OrderPrintForm extends BaseModel
{
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['order_id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $printOrder = new PrintOrder();
            $order = Order::findOne(['is_delete' => 0, 'id' => $this->order_id, 'mall_id' => \Yii::$app->mall->id]);
            $orderConfig = OrderCommon::getCommonOrder($order->sign)->getOrderConfig();
            if ($orderConfig->is_print == 0) {
                throw new PrintException('未开启打印设置，无法打印');
            }
            $data = $printOrder->print($order, $this->order_id, 'reprint');
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $data
            ];
        } catch (PrintException $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}
