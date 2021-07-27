<?php

namespace app\forms\common\order;

use app\events\OrderEvent;
use app\forms\common\template\tplmsg\Tplmsg;
use app\models\BaseModel;
use app\models\ClerkUser;
use app\models\ClerkUserStoreRelation;
use app\models\GoodsWarehouse;
use app\models\Model;
use app\models\Order;
use app\models\OrderClerk;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\plugins\baopin\models\BaopinMchClerkOrder;
use app\plugins\baopin\models\BaopinMchGoods;
use app\plugins\baopin\models\BaopinOrder;
use app\plugins\mch\models\Mch;


class OrderClerkCommon extends BaseModel
{
    public $id;
    public $action_type;
    public $clerk_remark;
    public $clerk_id;
    public $clerk_type;
    public $clerk_code;

    public function rules()
    {
        return [
            [['id', 'action_type', 'clerk_id', 'clerk_type'], 'required'],
            [['id', 'action_type', 'clerk_id', 'clerk_type'], 'integer'],
            [['clerk_remark'], 'string'],
        ];
    }

    public function affirmPay()
    {
        $beginTransaction = \Yii::$app->db->beginTransaction();
        try {
            /* @var Order $order */
            $order = Order::find()->where([
                'is_delete' => 0,
                'send_type' => 1,
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ])->one();

            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中,不能进行操作');
            }

            if ($order->cancel_status != 0) {
                throw new \Exception('订单取消中,无法收款');
            }

            if ($order->is_pay == 1) {
                throw new \Exception('订单已支付,下拉刷新页面数据');
            }

            $clerkUserIds = ClerkUserStoreRelation::find()
                ->where(['store_id' => $order->store_id])
                ->select('clerk_user_id');

            /** @var ClerkUser $clerkUser */
            $clerkUser = ClerkUser::find()->where([
                'user_id' => $this->clerk_id,
                'id' => $clerkUserIds,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'mch_id' => $order->mch_id
            ])->with('store')->asArray()->one();
            if (!$clerkUser) {
                throw new \Exception('用户不是核销员、无权限执行此操作');
            }

            $order->is_pay = 1;
            $order->pay_type = 2;
            $order->pay_time = date('Y-m-d H:i:s', time());
            $res = $order->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($order));
            }
            \Yii::$app->trigger(Order::EVENT_PAYED, new OrderEvent([
                'order' => $order
            ]));

            $orderClerk = new OrderClerk();
            $orderClerk->mall_id = \Yii::$app->mall->id;
            $orderClerk->affirm_pay_type = $this->action_type;
            $orderClerk->clerk_type = $this->clerk_type;
            $orderClerk->order_id = $order->id;
            $res = $orderClerk->save();

            if (!$res) {
                throw new \Exception($orderClerk);
            }

            $beginTransaction->commit();
            return true;
        } catch (\Exception $e) {
            $beginTransaction->rollBack();
            throw $e;
        }
    }

    public function orderClerk()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
//            if (!$this->clerk_remark) {
//                throw new \Exception('请填写核销备注');
//            }
            /** @var Order $order */
            $where = [
                'is_delete' => 0,
                'send_type' => 1,
//                'id'        => $this->id,
                'mall_id'   => \Yii::$app->mall->id,
            ];
            if(!empty($this->clerk_code)){
                $where['offline_qrcode'] = $this->clerk_code;
            }else{
                $where['id'] = (int)$this->id;
            }
            $order = Order::find()->with(['detail.goods.goodsWarehouse'])->where($where)->one();
            if (!$order) {
                throw new \Exception('订单不存在');
            }

            if ($order->status == 0) {
                throw new \Exception('订单进行中，不能进行操作');
            }

            if ($order->cancel_status == 2) {
                throw new \Exception('订单申请退款中');
            }

            if ($order->cancel_status == 1) {
                throw new \Exception('订单已退款');
            }

            if ($order->is_pay != 1) {
                throw new \Exception('订单未支付，请先进行收款');
            }

            if(!empty($order->clerk_id)){
                throw new \Exception('请勿重复核销操作');
            }

            $mch = Mch::findOne([
                'user_id'       => $this->clerk_id,
                'review_status' => Mch::REVIEW_STATUS_CHECKED
            ]);

            $store = Store::findOne(["mch_id" => $mch->id]);

            $hasPermission      = false;
            $baopinMchGoodsList = [];

            if($order->order_type == "offline_baopin"){ //爆品
                if($mch){ //用户是商户身份
                    $details = $order->detail;
                    if(!$details){
                        throw new \Exception('数据异常，订单详情不存在');
                    }
                    $hasPermission = true;
                    foreach($details as $detail){
                        //查找商家爆品库是否有货
                        $baopinMchGoods = BaopinMchGoods::findOne([
                            "mch_id"    => $mch->id,
                            "goods_id"  => $detail->goods_id,
                            "is_delete" => 0
                        ]);
                        if(!$baopinMchGoods){
                            $hasPermission = false;
                            break;
                        }
                        //判断是否有库存
                        if($baopinMchGoods->stock_num < $detail->num){
                            $hasPermission = false;
                            break;
                        }
                        $baopinMchGoodsList[] = [$detail->num, $baopinMchGoods];
                    }
                }
            }elseif($order->order_type == "offline_normal"){
                if($mch && $order->mch_id == $mch->id){
                    $hasPermission = true;
                }
            }

            if(!$hasPermission){
                $query = ClerkUserStoreRelation::find()->alias("cusr");
                $query->innerJoin("{{%clerk_user}} cu", "cu.user_id=cusr.clerk_user_id AND cu.is_delete=0");
                $hasPermission = $query->where([
                    'cusr.store_id'      => (int)$order->store_id,
                    'cusr.clerk_user_id' => $this->clerk_id
                ])->exists() ? true : false;
            }

            if (!$hasPermission) {
                throw new \Exception('没有核销权限，禁止核销');
            }

            $order->is_send     = 1;
            $order->send_at     = time();
            $order->is_confirm  = 1;
            $order->confirm_at  = time();
            $order->clerk_id    = $this->clerk_id;
            $order->store_id    = $store->id;

            if (!$order->save()) {
                throw new \Exception($this->responseErrorMsg($order));
            }

            $orderClerk = OrderClerk::find()->where(['order_id' => $order->id])->one();
            if (!$orderClerk) {
                $orderClerk = new OrderClerk();
                $orderClerk->mall_id          = \Yii::$app->mall->id;
                $orderClerk->affirm_pay_type  = $this->action_type;
                $orderClerk->order_id         = $order->id;
            }
            $orderClerk->clerk_remark = $this->clerk_remark ?: '';
            $orderClerk->clerk_type   = $this->clerk_type;
            if (!$orderClerk->save()) {
                throw new \Exception($this->responseErrorMsg($orderClerk));
            }

            if($baopinMchGoodsList){
                foreach($baopinMchGoodsList as list($num, $item)){
                    $baopinMchClerkOrder = new BaopinMchClerkOrder([
                        "mall_id"    => $item->mall_id,
                        "order_id"   => $order->id,
                        "goods_id"   => $item->goods_id,
                        "created_at" => time(),
                        "updated_at" => time(),
                        "mch_id"     => $item->mch_id,
                        "store_id"   => $item->store_id
                    ]);
                    if(!$baopinMchClerkOrder->save()){
                        throw new \Exception($this->responseErrorMsg($baopinMchClerkOrder));
                    }

                    $item->stock_num -= $num;
                    $item->updated_at = time();
                    if(!$item->save()){
                        throw new \Exception($this->responseErrorMsg($item));
                    }
                }
            }

            $transaction->commit();
            \Yii::$app->trigger(Order::EVENT_CONFIRMED, new OrderEvent([
                'order' => $order
            ]));
            //通知
            $tplMsg = new Tplmsg();
            $tplMsg->orderClerkTplMsg($order, '订单已核销');
//            return true;
            return [
                'name' => $order->detail[0]->goods->goodsWarehouse->name,
                'cover_pic' => $order->detail[0]->goods->goodsWarehouse->cover_pic,
                'total_original_price' => $order->detail[0]->total_original_price,
                'num' => $order->detail[0]->num,
                'offline_qrcode' => $order->offline_qrcode,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
