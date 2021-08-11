<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-处理分销的公共订单
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\order;

use app\component\jobs\CommonOrderJob;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\CommonOrder;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\distribution\jobs\DistributionLogJob;
use Yii;
use yii\base\Exception;

class CommonOrderForm extends BaseModel
{
    /** @var array 表单数据 */
    public $form_data;

    public function rules()
    {

    }

    /**
     * 添加
     * @return bool
     * @throws \Exception
     */
    public function addCommonOrder()
    {
        try {
            $commonOrderModel = new CommonOrder();
            $commonOrderModel->mall_id = $this->form_data["mall_id"];
            $commonOrderModel->user_id = $this->form_data["user_id"];
            $commonOrderModel->order_id = $this->form_data["order_id"];
            $commonOrderModel->order_type = is_int($this->form_data["order_type"]) ? "mall" : $this->form_data["order_type"];
            $commonOrderModel->pay_price = $this->form_data["pay_price"];
            if (!$commonOrderModel->save()) {
                throw new \Exception($this->responseErrorMsg($commonOrderModel));
            }
            $common_order_id = $commonOrderModel->id;
            return $common_order_id;
        } catch (\Exception $ex) {
            throw new \Exception(CommonLogic::getExceptionMessage($ex));
        }
    }

    /**
     * 更新订单
     * @param $updateData
     * @param $columns
     * @return bool
     */
    public static function updateCommonOrder($updateData, $columns)
    {
        try {
            $result = CommonOrder::edit($updateData, $columns);
            if ($result === false) {
                throw new \Exception("更新失败");
            }
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 更新订单
     * @param $updateData
     * @param $columns
     * @return bool
     */
    public static function updateCommonOrderDetail($updateData, $columns)
    {
        try {
            $result = CommonOrderDetail::edit($updateData, $columns);
            if ($result === false) {
                throw new \Exception("更新失败");
            }
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 添加公共订单详情
     * @param $common_order_id
     * @return bool
     * @throws \Exception
     */
    public function addCommonOrderDetail($common_order_id, $goodsItem)
    {
        $commonOrderDetailModel = new CommonOrderDetail();
        $commonOrderDetailModel->order_id = $this->form_data["order_id"];
        $commonOrderDetailModel->user_id = $this->form_data["user_id"];
        $commonOrderDetailModel->mall_id = $this->form_data["mall_id"];
        $commonOrderDetailModel->num = $this->form_data["num"];
        $commonOrderDetailModel->common_order_id = intval($common_order_id);
        $commonOrderDetailModel->price = $this->form_data["price"];
        $commonOrderDetailModel->attr_id = isset($goodsItem["goods_attr"]["id"]) ? $goodsItem["goods_attr"]["id"] : 0;
        $commonOrderDetailModel->goods_id = $this->form_data["goods_id"];
        $commonOrderDetailModel->goods_type = is_int($this->form_data["goods_type"]) ? "mall" : $this->form_data["goods_type"];
        $commonOrderDetailModel->status = CommonOrderDetail::STATUS_NORMAL;
        $commonOrderDetailModel->order_no = $this->form_data["order_no"];
        $commonOrderDetailModel->order_detail_id = intval($this->form_data["order_detail_id"]);
        if ($commonOrderDetailModel->goods_type == CommonOrderDetail::TYPE_MALL_GOODS) {
            $goods = Goods::findOne($commonOrderDetailModel->goods_id);
            if ($goods) {
                $commonOrderDetailModel->profit = floatval($commonOrderDetailModel->price) - floatval($goods->costPrice);
            }
            if ($commonOrderDetailModel->profit < 0) {
                $commonOrderDetailModel->profit = 0;
            }
        }

        $result = $commonOrderDetailModel->save();
        if ($result === false) {
            throw new \Exception((new BaseModel())->responseErrorMsg($commonOrderDetailModel));
            return false;
        }
        //使用触发器触发队列会出现两次重复，所以暂时直接push数据到队列
//        \Yii::$app->queue->delay(3)->push(new DistributionLogJob(['common_order_detail_id' => $commonOrderDetailModel->id, 'type' => 1]));
        return true;
    }

    /**
     * 公共订单任务调用方法
     * @param $order_id
     * @param $status
     * @param $sign
     * @param $mall_id
     * @param $user_id
     * @param $price
     * @param $goods_id
     * @param $num
     */
    public function commonOrderJob($order_id, $status, $sign = CommonOrderDetail::TYPE_MALL_GOODS, $mall_id = 0, $user_id = 0, $price = 0, $num = 1)
    {
        \Yii::warning("commonOrderForm user_id={$user_id} mall_id=" . $mall_id);
        $dataArr = [
            'order_id' => $order_id,
            'order_type' => $sign,
            'status' => $status,
            'user_id' => $user_id,
            'mall_id' => $mall_id,
            'price' => $price,
            'num' => $num
        ];
        \Yii::warning("commonOrderForm dataArr=" . var_export($dataArr, true));
        try{
            $class = new CommonOrderJob($dataArr);
            \Yii::$app->queue->delay(0)->push($class);
        }catch (\Exception $ex){
            \Yii::error("commonOrderForm commonOrderJob error=" . CommonLogic::getExceptionMessage($ex));
        }

    }

    /**
     * 批量处理公共订单任务
     * @param array $orders 多个订单列表
     * @param $status
     * @param int $sign
     * @param int $num
     */
    public function batchCommonOrderJob($orders,$status,$sign = CommonOrderDetail::TYPE_MALL_GOODS,$num = 1)
    {
        \Yii::warning("batchCommonOrderJob order=".var_export($orders,true));

        try{
            /** @var Order $order */
            foreach ($orders as $order){
                $dataArr = [
                    'order_id' => $order->id,
                    'order_type' => $sign,
                    'status' => $status,
                    'user_id' => $order->user_id,
                    'mall_id' => $order->mall_id,
                    'price' => $order->total_pay_price,
                    'num' => $num
                ];
                \Yii::warning("commonOrderForm batchCommonOrderJob dataArr=" . var_export($dataArr, true));
                $class = new CommonOrderJob($dataArr);
                \Yii::$app->queue->delay(0)->push($class);
            }
        }catch (\Exception $ex){
            \Yii::error("commonOrderForm batchCommonOrderJob error=" . CommonLogic::getExceptionMessage($ex));
        }

    }

    /**
     * 添加公共订单详情，不走队列
     * @Author bing
     * @DateTime 2020-09-25 17:08:21
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $order_id
     * @param [type] $status
     * @param [type] $sign
     * @param integer $mall_id
     * @param integer $user_id
     * @param integer $pay_price
     * @param integer $num
     * @return void
     */
    public function createCommonOrder($order_id, $status, $sign = CommonOrderDetail::TYPE_MALL_GOODS, $mall_id = 0, $user_id = 0, $pay_price = 0, $num = 1){
        try{
            //新增
            if($status == CommonOrderDetail::STATUS_NORMAL){
                $orders = CommonOrder::getOneData(["order_id" => $order_id,"is_delete" => 0]);
                if(empty($orders)){
                    $commonOrderForm = new self();
                    $commonOrderForm->form_data["order_id"]  = $order_id;
                    $commonOrderForm->form_data["order_type"]  = $sign;
                    $commonOrderForm->form_data["order_id"]  = $order_id;
                    $commonOrderForm->form_data["pay_price"]  = $pay_price;
                    $commonOrderForm->form_data["mall_id"] = $mall_id;
                    $commonOrderForm->form_data["user_id"] = $user_id;
                    $commonOrderId = $commonOrderForm->addCommonOrder();

                    if($commonOrderId){
                        $orderDetailList = OrderDetail::find()->where(["order_id" => $order_id,"is_delete" => 0])->with(["order"])->asArray()->all();
                        if(!empty($orderDetailList)){
                            foreach ($orderDetailList as $value){
                                $commonOrderForm = new self();
                                $commonOrderForm->form_data["order_id"] = $order_id;
                                $commonOrderForm->form_data["order_detail_id"] = $value["id"];
                                $commonOrderForm->form_data["order_no"] = isset($value["order"]) ? $value["order"]["order_no"] : "";
                                $commonOrderForm->form_data["price"] = $value["total_price"];
                                $commonOrderForm->form_data["num"] = $value["num"];
                                $commonOrderForm->form_data["goods_type"] = $sign;
                                $commonOrderForm->form_data["goods_id"] = $value["goods_id"];
                                $commonOrderForm->form_data["user_id"] = $user_id;
                                $commonOrderForm->form_data["mall_id"] = $mall_id;
                                $goods_infos = !empty($value["goods_info"]) ? json_decode($value["goods_info"],true) : [];
                                $result = $commonOrderForm->addCommonOrderDetail($commonOrderId,$goods_infos);
                                if(!$result){
                                    throw new \Exception("公共订单详情添加失败");
                                }
                            }
                        }else{
                            $commonOrderForm = new self();
                            $commonOrderForm->form_data["order_id"] = $order_id;
                            $commonOrderForm->form_data["order_detail_id"] = 0;
                            $commonOrderForm->form_data["num"] = $num;
                            $commonOrderForm->form_data["order_no"] = "";
                            $commonOrderForm->form_data["price"] = $pay_price;
                            $commonOrderForm->form_data["goods_type"] = $sign;
                            $commonOrderForm->form_data["goods_id"] = 0;
                            $commonOrderForm->form_data["user_id"] = $user_id;
                            $commonOrderForm->form_data["mall_id"] = $mall_id;
                            $result = $commonOrderForm->addCommonOrderDetail($commonOrderId,[]);
                            if(!$result){
                                throw new \Exception("公共订单详情添加失败");
                            }
                        }
                    }
                }
            }else if($status == CommonOrderDetail::STATUS_INVALID || $status == CommonOrderDetail::STATUS_COMPLETE){//退款或订单取消，订单过售后
                $result = CommonOrderForm::updateCommonOrder(["status" => $status],["order_id" => $order_id]);
                if(!$result){
                    throw new \Exception("公共订单更新失败");
                }
                $result = CommonOrderForm::updateCommonOrderDetail(["status" => $status],["order_id" => $order_id]);
                if(!$result){
                    throw new \Exception("公共订单详情更新失败");
                }
            }
        }catch (\Exception $ex){
            \Yii::error("commonOrderForm commonOrderJob error=" . CommonLogic::getExceptionMessage($ex));
        }
    }
}
