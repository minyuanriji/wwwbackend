<?php

namespace app\commands\commission_action;


use app\core\ApiCode;
use app\forms\common\UserIncomeCommissionHotel3rForm;
use app\models\User;
use app\plugins\commission\models\CommissionHotel3rPriceLog;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\Hotels;
use yii\base\Action;

class Hotel3rAction extends Action{

    public function run(){
        while (true){
            if(!$this->doNew()){
                $this->doStatusChanged();
            }
        }
    }

    //新增操作
    private function doNew(){
        $hotelOrder = HotelOrder::find()->andWhere([
            "AND",
            ["pay_status" => "paid"],
            ["IN", "order_status", ["finished", "success", "unconfirmed"]],
            ["commission_3r_status" => 0]
        ])->orderBy("updated_at ASC")->one();

        if(!$hotelOrder){
            return false;
        }

        //更新时间，防止一个出错反复执行
        $hotelOrder->updated_at = time();
        $hotelOrder->save();

        $trans = \Yii::$app->db->beginTransaction();
        try {

            //获取酒店信息
            $hotel = Hotels::findOne($hotelOrder->hotel_id);
            if(!$hotel || $hotel->is_delete){
                $hotelOrder->commission_3r_status = 1;
                throw new \Exception("酒店不存在");
            }

            //计算利润
            $profit = max(0, $hotelOrder->order_price * ($hotel->transfer_rate/100 - 0.1) * 0.6);
            if($profit <= 0){
                $hotelOrder->commission_3r_status = 1;
                throw new \Exception("利润小或等于0无法分佣");
            }

            //要分佣的父数据
            $parentDatas = $this->controller->getCommissionParentRuleDatas($hotelOrder->user_id, $hotel->id, 'hotel_3r');

            //通过相关规则键获取分佣规则进行分佣
            foreach($parentDatas as $parentData){
                $ruleData = $parentData['rule_data'];

                //无分佣规则 跳过
                if(!$ruleData) continue;

                //父级用户信息
                $user = User::findOne($parentData['id']);
                if(!$user || $user->is_delete){
                    continue;
                }

                //计算分佣金额
                if($ruleData['commission_type'] == 1){ //按百分比
                    $price = (floatval($ruleData['commisson_value'])/100) * floatval($profit);
                }else{ //按固定值
                    $price = (float)$ruleData['commisson_value'];
                }

                //根据酒店入住天数生成待结算分佣记录
                $startTime = strtotime($hotelOrder->booking_start_date . " 00:00:00");
                if($hotelOrder->booking_days > 0){
                    $price = $price/$hotelOrder->booking_days;
                    for($i=1; $i <= $hotelOrder->booking_days; $i++){
                        $date = date("Y-m-d", $startTime + 3600 * 24 * $i);
                        $uniqueData = [
                            'mall_id'        => $hotelOrder->mall_id,
                            'hotel_order_id' => $hotelOrder->id,
                            'user_id'        => $parentData['id'],
                            'date'           => $date
                        ];
                        $priceLog = CommissionHotel3rPriceLog::findOne($uniqueData);
                        if(!$priceLog){
                            $priceLog = new CommissionHotel3rPriceLog(array_merge($uniqueData, [
                                'created_at'     => time(),
                                'updated_at'     => time(),
                                'price'          => $price,
                                'status'         => 0,
                                'rule_data_json' => json_encode(json_encode($ruleData))
                            ]));
                            if(!$priceLog->save()){
                                throw new \Exception(json_encode($priceLog->getErrors()));
                            }

                            $res = UserIncomeCommissionHotel3rForm::hotelCommissionFzAdd($user, $hotel, $priceLog, false);
                            if($res['code'] != ApiCode::CODE_SUCCESS){
                                throw new \Exception($res['msg']);
                            }
                        }
                    }
                }
            }

            $hotelOrder->commission_3r_status = 1;

            $trans->commit();

        }catch (\Exception $e){
            $trans->rollBack();
            $hotelOrder->commission_3r_remark = substr($e->getMessage(), 0, 255);
            $this->controller->commandOut($e->getMessage());
        }

        $hotelOrder->updated_at = time();
        if(!$hotelOrder->save()){
            $this->controller->commandOut(json_encode($hotelOrder->getErrors()));
        }

        $this->controller->commandOut("新增酒店订单：" . $hotelOrder->id . "消费分佣记录");

        return true;
    }

    //状态改变
    private function doStatusChanged(){
        $query = HotelOrder::find()->alias("ho")->andWhere([
            "AND",
            ["IN", "ho.pay_status", ["refunding", "paid", "refund"]],
            ["IN", "ho.order_status", ["finished", "success", "unconfirmed"]],
            ["ho.commission_3r_status" => 1],
            "(UNIX_TIMESTAMP(ho.booking_start_date) + (ho.booking_days+1) * 3600 * 24) < '".time()."'"
        ]);
        $query->innerJoin(["h" => Hotels::tableName()], "h.id=ho.hotel_id");
        $query->leftJoin(["cpl" => CommissionHotel3rPriceLog::tableName()], "cpl.hotel_order_id=ho.id AND cpl.status=0");

        $selects = ["h.name", "cpl.hotel_order_id", "ho.pay_status", "ho.order_status", "ho.booking_start_date", "ho.booking_days", "ho.real_booking_days"];

        $orderData = $query->groupBy("cpl.hotel_order_id")
                        ->having("count(cpl.id) > 0")
                        ->asArray()->select($selects)
                        ->orderBy("ho.updated_at asc")->one();
        if(!$orderData){
            return false;
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            //订单不是支付状态，待结算分佣取消
            if($orderData['pay_status'] != "paid"){
                $rows = CommissionHotel3rPriceLog::find()->where([
                    "hotel_order_id" => $orderData['hotel_order_id'],
                    "status" => 0
                ])->select(["id", "user_id"])->all();
                $userPriceLogIds = [];
                foreach($rows as $row){
                    $userPriceLogIds[$row['user_id']][] = $row['id'];
                }
                foreach($userPriceLogIds as $userId => $priceLogIds){
                    UserIncomeCommissionHotel3rForm::cancelHotelOrderCommissionFz($userId, $priceLogIds, false);
                }
            }else{

                $diff = $orderData['booking_days'] - $orderData['real_booking_days'];

                //获取待结算分佣记录
                $priceLogs = CommissionHotel3rPriceLog::find()->where([
                    "hotel_order_id" => $orderData['hotel_order_id'],
                    "status"         => 0
                ])->orderBy("date DESC")->all();
                $userPriceLogs = [];
                foreach($priceLogs as $priceLog){
                    $userPriceLogs[$priceLog->user_id][] = $priceLog;
                }
                foreach($userPriceLogs as $userId => $priceLogs){
                    $diffIds = [];
                    $user = User::findOne($userId);
                    foreach($priceLogs as $priceLog){
                        if(!$user || count($diffIds) < $diff){
                            $diffIds[] = $priceLog->id;
                        }else{
                            $res = UserIncomeCommissionHotel3rForm::confirmHotelCommissionFz($user, $orderData['name'], $priceLog, false);
                            if($res['code'] != ApiCode::CODE_SUCCESS){
                                throw new \Exception($res['msg']);
                            }
                        }
                    }
                    if($diffIds){
                        $res = UserIncomeCommissionHotel3rForm::cancelHotelOrderCommissionFz($userId, $diffIds, false);
                        if($res['code'] != ApiCode::CODE_SUCCESS){
                            throw new \Exception($res['msg']);
                        }
                    }
                }

            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
        }

        $this->controller->commandOut("酒店订单：" . $orderData['hotel_order_id'] . "消费分佣处理完毕");

    }
}