<?php
namespace app\commands\hotel_task_action;

use app\core\ApiCode;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;
use yii\base\Action;

class QueryAction extends Action{

    public function run(){
        while(true){
            try {
                //查询状态为已支付、待确认的订单
                $hotelOrder = HotelOrder::find()->where([
                    "order_status" => "unconfirmed",
                    "pay_status"   => "paid"
                ])->orderBy("updated_at ASC")->one();
                if($hotelOrder){

                    $hotelOrder->updated_at = time();
                    $hotelOrder->save();

                    $plateform = $hotelOrder->getPlateform();
                    if(!$plateform){
                        throw new \Exception("订单:".$hotelOrder->id."无法获取平台信息");
                    }
                    $res = OrderHelper::queryPlateformOrder($hotelOrder, $plateform);
                    if($res['code'] != ApiCode::CODE_SUCCESS){
                        throw new \Exception($res['msg']);
                    }

                    $oldOrderStatus = $hotelOrder->order_status;

                    //预订成功：1预订成功/3预订未到/4已入住/5已完成
                    if(in_array($res['data']['order_state'], [1, 3, 4, 5])){
                        $hotelOrder->order_status = "success";
                    }

                    //已取消
                    if(in_array($res['data']['order_state'], [2])){
                        $hotelOrder->order_status = "cancel";
                    }

                    //确认失败
                    if(in_array($res['data']['order_state'], [6])){
                        $hotelOrder->order_status = "fail";
                    }

                    if(!$hotelOrder->save()){
                        throw new \Exception(@json_encode($hotelOrder->getErrors()));
                    }

                    if($oldOrderStatus != $hotelOrder->order_status){
                        echo "Hotel Order [ID:". $hotelOrder->id . "] Status Sync Successfully\n";
                    }
                }
            }catch (\Exception $e){
                echo $e->getMessage() . "\n";
            }
            sleep(1);
        }
    }
}