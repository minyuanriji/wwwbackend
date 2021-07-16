<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单取消表单
 * Author: zal
 * Date: 2020-04-13
 * Time: 17:16
 */

namespace app\forms\common\order;

use app\core\ApiCode;
use app\events\OrderEvent;
use app\forms\common\template\tplmsg\Tplmsg;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;

class OrderCancelForm extends BaseModel
{
    public $order_id;
    public $remark;
    public $status;
    public $mch_id;
    /** @var int 操作来源1前台2后台 */
    public $from = 2;

    public function rules()
    {
        return [
            [['order_id', 'status'], 'required'],
            [['order_id', 'status', 'mch_id'], 'integer'],
            [['remark'], 'string'],
        ];
    }

    /**
     * 取消订单
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $transactioan = \Yii::$app->db->beginTransaction();
        try {
            /** @var Order $order */
            $order = Order::getOrderInfo([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->order_id,
                'mch_id' => $this->mch_id ?: ($this->from == 1 ? \Yii::$app->user->identity->mch_id : \Yii::$app->admin->identity->mch_id),
                'is_delete' => 0,
                'is_send' => 0,
                'is_sale' => 0,
                'is_confirm' => 0
            ]);

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status > 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            // 拒绝
            if ($this->status == 2) {
                $order->cancel_status = 0;
                $order->words = $this->remark;
            }

            // 同意
            if ($this->status == 1) {
                $order->status = Order::STATUS_CLOSE;
                $order->words = $this->remark;
                $order->cancel_status = 1;
                $order->cancel_at = time();
            }

            if (!$order->save()) {
                throw new \Exception($this->responseErrorMsg($order));
            }

            if ($this->status == 1) {
                \Yii::$app->trigger(Order::EVENT_CANCELED, new OrderEvent([
                    'order' => $order
                ]));
            }
            /*if ($this->status == 2) {
                try {
                    $template = new Tplmsg();
                    $template->orderCancelMsg($order);
                } catch (\Exception $exception) {
                    \Yii::error('模板消息发送: ' . $exception->getMessage());
                }
            }*/
            $transactioan->commit();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"操作成功");
        } catch (\Exception $e) {
            $transactioan->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}
