<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\taolijin\models\TaolijinAli;
use app\plugins\taolijin\models\TaolijinOrders;

class TaoLiJinOrderAddForm extends BaseModel{

    public $ali_id;
    public $user_id;
    public $o_status;
    public $pay_price;
    public $pay_at;
    public $created_at;
    public $ali_order_sn;
    public $ali_item_id;
    public $ali_item_name;
    public $ali_item_price;
    public $ali_item_pic;
    public $ali_commission_rate;
    public $ali_commission_price;

    public function rules(){
        return array_merge(parent::rules(), [
            [['ali_id', 'user_id', 'o_status', 'pay_price', 'pay_at', 'created_at', 'ali_order_sn',  'ali_item_id',
              'ali_item_name', 'ali_item_price', 'ali_item_pic', 'ali_commission_rate', 'ali_commission_price'], 'required']
        ]);
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟不存在");
            }

            $user = User::findOne($this->user_id);
            if(!$user){
                throw new \Exception("用户不存在");
            }

            $order = TaolijinOrders::findOne([
                "ali_id"       => $aliModel->id,
                "ali_order_sn" => $this->ali_order_sn
            ]);
            if($order && !$order->is_delete){
                throw new \Exception("请不要重复录入订单");
            }

            if(!$order){
                $order = new TaolijinOrders([
                    "mall_id"    => $aliModel->mall_id,
                    "ali_id"     => $aliModel->id,
                    "user_id"    => $user->id,
                    "created_at" => time()
                ]);
            }
            $order->pay_price            = $this->pay_price;
            $order->pay_at               = $this->pay_at;
            $order->ali_order_sn         = $this->ali_order_sn;
            $order->ali_item_id          = $this->ali_item_id;
            $order->ali_item_name        = $this->ali_item_name;
            $order->ali_item_pic         = $this->ali_item_pic;
            $order->ali_item_price       = $this->ali_item_price;
            $order->ali_commission_price = $this->ali_commission_price;
            $order->ali_commission_rate  = $this->ali_commission_rate;
            $order->ali_created_at       = $this->created_at;
            $order->updated_at           = time();
            $order->is_delete            = 0;

            //订单状态
            if($this->o_status == "paid"){
                $order->order_status = "paid";
                $order->pay_status = "paid";
            }elseif($this->o_status == "finished"){
                $order->order_status = "paid";
                $order->pay_status = "paid";
            }

            //判断一下佣金比例是否正确
            if($order->ali_commission_rate < 0 || $order->ali_commission_rate > 100){
                throw new \Exception("请设置佣金比例范围在0至100之间");
            }

            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}