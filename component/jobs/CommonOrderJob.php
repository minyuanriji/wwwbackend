<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 公共订单任务
 * Author: zal
 * Date: 2020-06-30
 * Time: 10:01
 */

namespace app\component\jobs;

use app\events\CommonOrderDetailEvent;
use app\forms\api\order\CommonOrderForm;
use app\handlers\CommonOrderDetailHandler;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Mall;
use app\models\Order;
use app\models\OrderDetail;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\queue\JobInterface;
use yii\queue\Queue;

class CommonOrderJob extends Component implements JobInterface
{

    public $order_type;
    public $order_id;
    public $status;
    public $user_id;
    public $mall_id;
    public $price = 0;
    public $goods_id = 0;
    public $num = 0;
    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        \Yii::warning("---CommonOrderJob start---");
        // TODO: Implement execute() method.
        $t = \Yii::$app->db->beginTransaction();
        try{
            $mall = Mall::findOne(['id' => $this->mall_id]);
            \Yii::$app->setMall($mall);
            \Yii::warning("CommonOrderJob user_id={$this->user_id} mall_id=".$this->mall_id.";status=".$this->status);
            //新增
            if($this->status == CommonOrderDetail::STATUS_NORMAL){
                $orders = CommonOrder::getOneData(["order_id" => $this->order_id,"is_delete" => 0]);
                \Yii::warning("CommonOrderJob orders=".var_export($orders,true));
                if(empty($orders)){
                    \Yii::warning("CommonOrderJob orders add");
                    $commonOrderForm = new CommonOrderForm();
                    $commonOrderForm->form_data["order_id"]  = $this->order_id;
                    $commonOrderForm->form_data["order_type"]  = $this->order_type;
                    $commonOrderForm->form_data["order_id"]  = $this->order_id;
                    $commonOrderForm->form_data["pay_price"]  = $this->price;
                    $commonOrderForm->form_data["mall_id"] = $this->mall_id;
                    $commonOrderForm->form_data["user_id"] = $this->user_id;
                    $commonOrderId = $commonOrderForm->addCommonOrder();

                    if($commonOrderId){
                        $this->addCommonOrderDetail($commonOrderId);
                    }
                }
            }else if($this->status == CommonOrderDetail::STATUS_INVALID || $this->status == CommonOrderDetail::STATUS_COMPLETE){//退款或订单取消，订单过售后
                \Yii::warning("CommonOrderJob update status=".$this->status);
                $result = CommonOrderForm::updateCommonOrder(["status" => $this->status],["order_id" => $this->order_id]);
                if(!$result){
                    throw new \Exception("公共订单更新失败");
                }
                $result = CommonOrderForm::updateCommonOrderDetail(["status" => $this->status],["order_id" => $this->order_id]);
                if(!$result){
                    throw new \Exception("公共订单详情更新失败");
                }
            }
            $t->commit();
            \Yii::warning("---CommonOrderJob end---");
        }catch (\Exception $ex){

            \Yii::warning("---CommonOrderJob error File:".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());


            $t->rollBack();
            \Yii::error("---CommonOrderJob error File:".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());
        }
    }

    /**
     * 添加公共订单详情
     * @param $commonOrderId
     * @return bool
     * @throws \Exception
     */
    private function addCommonOrderDetail($commonOrderId){
        try{
            \Yii::warning("CommonOrderJob addCommonOrderDetail start");
            $orderDetailList = OrderDetail::find()->where(["order_id" => $this->order_id,"is_delete" => 0])->with(["order"])->asArray()->all();
            if(!empty($orderDetailList)){
                foreach ($orderDetailList as $value){
                    $commonOrderForm = new CommonOrderForm();
                    $commonOrderForm->form_data["order_id"] = $this->order_id;
                    $commonOrderForm->form_data["order_detail_id"] = $value["id"];
                    $commonOrderForm->form_data["order_no"] = isset($value["order"]) ? $value["order"]["order_no"] : "";
                    $commonOrderForm->form_data["price"] = $value["total_price"];
                    $commonOrderForm->form_data["num"] = $value["num"];
                    $commonOrderForm->form_data["goods_type"] = $this->order_type;
                    $commonOrderForm->form_data["goods_id"] = $value["goods_id"];
                    $commonOrderForm->form_data["user_id"] = $this->user_id;
                    $commonOrderForm->form_data["mall_id"] = $this->mall_id;
                    $goods_infos = !empty($value["goods_info"]) ? json_decode($value["goods_info"],true) : [];
                    $result = $commonOrderForm->addCommonOrderDetail($commonOrderId,$goods_infos);
                    if(!$result){
                        throw new \Exception("公共订单详情添加失败");
                    }
                }
            }else{
                $commonOrderForm = new CommonOrderForm();
                $commonOrderForm->form_data["order_id"] = $this->order_id;
                $commonOrderForm->form_data["order_detail_id"] = 0;
                $commonOrderForm->form_data["num"] = $this->num;
                $commonOrderForm->form_data["order_no"] = "";
                $commonOrderForm->form_data["price"] = $this->price;
                $commonOrderForm->form_data["goods_type"] = $this->order_type;
                $commonOrderForm->form_data["goods_id"] = $this->goods_id;
                $commonOrderForm->form_data["user_id"] = $this->user_id;
                $commonOrderForm->form_data["mall_id"] = $this->mall_id;
                $result = $commonOrderForm->addCommonOrderDetail($commonOrderId,[]);
                if(!$result){
                    throw new \Exception("公共订单详情添加失败");
                }
            }
        }catch (\Exception $ex){
            \Yii::error("---CommonOrderJob addCommonOrderDetail error File:".$ex->getFile().";Line:".$ex->getLine().";message:".$ex->getMessage());
            throw new \Exception($ex->getMessage());
        }
        return true;
    }
}