<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderClerk;
use app\models\OrderClerkExpress;
use app\models\OrderClerkExpressDetail;
use app\models\OrderDetail;
use app\models\User;

class OrderClerkDetailForm extends BaseModel
{
    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $clerkIds = explode(",", $this->id);

            $result = [];

            $query = OrderDetail::find()->alias("od");
            $query->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
            $query->innerJoin(["oc" => OrderClerk::tableName()], "oc.order_id=o.id");

            $query->andWhere(["IN", "oc.id", $clerkIds]);

            $result['details'] = [];
            $details = $query->asArray()->select("od.*")->all();
            if($details){
                foreach($details as $detail){
                    $goodsInfo = @json_decode($detail['goods_info'], true);
                    $detail['goods_info'] = $goodsInfo;
                    $result['details'][] = ArrayHelper::toArray($detail);
                }
            }

            $result['order']   = [];
            $result['user']    = [];
            $result['express'] = [];

            if(count($clerkIds) == 1){

                $orderClerk = OrderClerk::findOne($this->id);
                if(!$orderClerk || $orderClerk->is_delete){
                    throw new \Exception("无法获取核销记录");
                }

                $order = Order::findOne($orderClerk->order_id);
                if(!$order){
                    throw new \Exception("无法获取订单信息");
                }
                $result['order'] = ArrayHelper::toArray($order);

                $user = User::findOne($order->clerk_id);
                if(!$user){
                    throw new \Exception("无法获取到核销员信息");
                }
                $result['user'] = ArrayHelper::toArray($user);

                $orderClerkExpress = OrderClerkExpress::findOne(["order_id" => $orderClerk->order_id]);
                if($orderClerkExpress){
                    $expressDetail = OrderClerkExpressDetail::findOne($orderClerkExpress->express_detail_id);
                    if($expressDetail){
                        $result['express'] = ArrayHelper::toArray($expressDetail);
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result
            ];
        }catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}