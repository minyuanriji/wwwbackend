<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\baopin\models\BaopinMchGoods;

class OrderClerkSendForm extends BaseModel{

    public $details_id;
    public $send_type;
    public $express;
    public $express_code;
    public $express_no;
    public $express_content;

    public function rules(){
        return [
            [['details_id', 'send_type'], 'required'],
            [['express', 'express_code', 'express_no', 'express_content'], 'safe']
        ];
    }

    public function send(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $trans = \Yii::$app->db->beginTransaction();

        try {

            $detailIds = explode(",", $this->details_id);
            $orderDetails = OrderDetail::find()->andWhere([
                "AND",
                ["is_delete" => 0],
                ["is_refund" => 0],
                ["IN", "id", $detailIds]
            ])->all();
            if(!$orderDetails){
                throw new \Exception("订单详情记录不存在");
            }

            $orders = [];
            foreach($orderDetails as $orderDetail){

                if(!isset($orders[$orderDetail->order_id])){
                    $orders[$orderDetail->order_id] = Order::findOne($orderDetail->order_id);
                    if(!$orders[$orderDetail->order_id] || $orders[$orderDetail->order_id]->is_delete) {
                        throw new \Exception("订单”".$orderDetail->order_id."“不存在");
                    }
                }

                $order = $orders[$orderDetail->order_id];
                if(!$order->is_pay){
                    throw new \Exception("订单未支付");
                }

                //获取对应的爆品记录
                $baopinMchGoods = BaopinMchGoods::findOne([
                    "store_id" => $order->store_id,
                    "goods_id" => $orderDetail->goods_id
                ]);
                if(!$baopinMchGoods){
                    throw new \Exception("无法获取订单详情ID为".$orderDetail->id."的爆品记录");
                }

                $stockNum = $baopinMchGoods->stock_num;
                $totalStock = $baopinMchGoods->total_stock;

                $stockNum += $orderDetail->num;
                if($stockNum > $totalStock){
                    throw new \Exception("剩余库存不能大于总库存");
                }

                $form = new OrderClerkExpressForm([
                    "order_id"        => $orderDetail->order_id,
                    "order_detail_id" => $orderDetail->id,
                    "goods_id"        => $orderDetail->goods_id,
                    "send_type"       => $this->send_type,
                    "express_no"      => $this->express_no,
                    "express_content" => $this->express_content,
                    "express"         => $this->express,
                    "express_code"    => $this->express_code,
                    "store_id"        => $order->store_id
                ]);
                $res = $form->save();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }

                $baopinMchGoods->updated_at = time();
                $baopinMchGoods->stock_num += $orderDetail->num;
                if(!$baopinMchGoods->save()){
                    throw new \Exception($this->responseErrorMsg($baopinMchGoods));
                }
            }

            $trans->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}