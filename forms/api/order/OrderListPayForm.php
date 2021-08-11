<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-订单列表支付
 * Author: zal
 * Date: 2020-05-11
 * Time: 19:50
 */

namespace app\forms\api\order;


use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\Order;

class OrderListPayForm extends OrderPayFormBase
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    /**
     * 获取订单支付数据
     * @return array
     */
    public function loadPayData()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo($this);
        }
        try {
            $order = Order::getOrderInfo([
                'id' => $this->id,
                'user_id' => \Yii::$app->user->id,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'is_pay' => 0,
                'cancel_status' => 0,
                'is_confirm' => 0,
                'is_sale' => 0,
            ]);
            if (!$order) {
                throw new \Exception('订单数据异常,无法支付');
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',$this->getReturnData([$order]));
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}
