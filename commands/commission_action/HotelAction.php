<?php

namespace app\commands\commission_action;


use app\core\ApiCode;
use app\forms\common\UserIncomeCommissionHotelForm;
use app\models\User;
use app\plugins\commission\models\CommissionHotelPriceLog;
use app\plugins\commission\models\CommissionRuleChain;
use app\plugins\commission\models\CommissionRules;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\Hotels;
use yii\base\Action;

class HotelAction extends Action{

    public function run(){
        while (true){
            if(!$this->doNew()){
                $this->doStatusChanged();
            }
        }
    }

    //新增分佣记录
    private function doNew(){

        $hotelOrder = HotelOrder::find()->andWhere([
            "AND",
            ["pay_status" => "paid"],
            ["IN", "order_status", ["finished", "success", "unconfirmed"]],
            ["commission_status" => 0]
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
                $hotelOrder->commission_status = 1;
                throw new \Exception("酒店不存在");
            }

            //酒店推荐人
            $recommander = User::findOne($hotel->recommander_uid);
            if(!$recommander || $recommander->is_delete){
                $hotelOrder->commission_status = 1;
                throw new \Exception("酒店推荐人不存在");
            }

            //计算利润
            $profit = max(0, $hotelOrder->order_price * ($hotel->transfer_rate/100 - 0.1) * 0.6);
            if($profit <= 0){
                $hotelOrder->commission_status = 1;
                throw new \Exception("利润小或等于0无法分佣");
            }

            //独立分佣规则
            $rule = CommissionRules::find()->where([
                "item_type"      => "hotel",
                "item_id"        => $hotel->id,
                "apply_all_item" => 0,
                "is_delete"      => 0
            ])->one();
            if(!$rule){
                $rule = CommissionRules::find()->where([
                    "item_type"      => "hotel",
                    "apply_all_item" => 1,
                    "is_delete"      => 0
                ])->one();
            }

            if(!$rule){
                $hotelOrder->commission_status = 1;
                throw new \Exception("分佣规则不存在");
            }

            //根据所属等级获取规则链
            $ruleChain = CommissionRuleChain::find()->where([
                "rule_id"    => $rule->id,
                "level"      => 1,
                'role_type'  => $recommander->role_type,
                "unique_key" => $recommander->role_type . "#all"
            ])->one();
            if(!$ruleChain){
                $hotelOrder->commission_status = 1;
                throw new \Exception("分佣规则链<".$recommander->role_type . "#all>不存在");
            }

            //计算分佣金额
            if($rule->commission_type == 1){ //按百分比
                $price = (floatval($ruleChain->commisson_value)/100) * floatval($profit);
            }else{ //按固定值
                $price = (float)$ruleChain->commisson_value;
            }
            if($price <= 0){
                $hotelOrder->commission_status = 1;
                throw new \Exception("分佣金额小或等于0无法分佣");
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
                        'user_id'        => $recommander->id,
                        'date'           => $date
                    ];
                    $priceLog = CommissionHotelPriceLog::findOne($uniqueData);
                    if(!$priceLog){
                        $priceLog = new CommissionHotelPriceLog(array_merge($uniqueData, [
                            'created_at'     => time(),
                            'updated_at'     => time(),
                            'price'          => $price,
                            'status'         => 0,
                            'rule_data_json' => json_encode(array_merge($rule->getAttributes(),
                                $ruleChain->getAttributes()))
                        ]));
                        if(!$priceLog->save()){
                            throw new \Exception(json_encode($priceLog->getErrors()));
                        }

                        $res = UserIncomeCommissionHotelForm::hotelCommissionFzAdd($recommander, $hotel, $priceLog, false);
                        if($res['code'] != ApiCode::CODE_SUCCESS){
                            throw new \Exception($res['msg']);
                        }
                    }

                }
            }

            $hotelOrder->commission_status = 1;

            $trans->commit();

        }catch (\Exception $e){
            $trans->rollBack();
            $hotelOrder->commission_remark = substr($e->getMessage(), 0, 255);
            $this->controller->commandOut($e->getMessage());
        }

        $hotelOrder->updated_at = time();
        if(!$hotelOrder->save()){
            $this->controller->commandOut(json_encode($hotelOrder->getErrors()));
        }

        $this->controller->commandOut("新增酒店订单：" . $hotelOrder->id . "推荐分佣记录");

        return true;
    }

    //状态改变
    private function doStatusChanged(){

        $query = HotelOrder::find()->alias("ho")->andWhere([
            "AND",
            ["IN", "ho.pay_status", ["refunding", "paid", "refund"]],
            ["IN", "ho.order_status", ["finished", "success", "unconfirmed"]],
            ["ho.commission_status" => 1],
            "(UNIX_TIMESTAMP(ho.booking_start_date) + (ho.booking_days+1) * 3600 * 24) < '".time()."'"
        ]);
        $query->innerJoin(["h" => Hotels::tableName()], "h.id=ho.hotel_id");
        $query->leftJoin(["cpl" => CommissionHotelPriceLog::tableName()], "cpl.hotel_order_id=ho.id AND cpl.status=0");

        $selects = ["h.recommander_uid", "h.name", "cpl.hotel_order_id", "ho.pay_status", "ho.order_status", "ho.booking_start_date", "ho.booking_days", "ho.real_booking_days"];

        $orderData = $query->groupBy("cpl.hotel_order_id")
                          ->having("count(cpl.id) > 0")
                          ->asArray()->select($selects)
                          ->orderBy("ho.updated_at asc")->one();

        if(!$orderData){
            return false;
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            //如果用户不存在或订单不是支付状态，待结算分佣取消
            $user = User::findOne($orderData['recommander_uid']);
            if($orderData['pay_status'] != "paid" || !$user || $user->is_delete){
                $priceLogIds = CommissionHotelPriceLog::find()->where([
                    "hotel_order_id" => $orderData['hotel_order_id'],
                    "status" => 0
                ])->select(["id"])->column();
                UserIncomeCommissionHotelForm::cancelHotelOrderCommissionFz($user ? $user->id : 0, $priceLogIds, false);
            }else{

                //获取待结算分佣记录
                $priceLogs = CommissionHotelPriceLog::find()->where([
                    "hotel_order_id" => $orderData['hotel_order_id'],
                    "status"         => 0
                ])->orderBy("date DESC")->all();

                $diff = $orderData['booking_days'] - $orderData['real_booking_days'];
                $diffIds = [];
                foreach($priceLogs as $priceLog){
                    if(count($diffIds) < $diff){
                        $diffIds[] = $priceLog->id;
                    }else{
                        $res = UserIncomeCommissionHotelForm::confirmHotelCommissionFz($user, $orderData['name'], $priceLog, false);
                        if($res['code'] != ApiCode::CODE_SUCCESS){
                            throw new \Exception($res['msg']);
                        }
                    }
                }

                //实际入住天数未达到预订天数处理
                if($diffIds){
                    $res = UserIncomeCommissionHotelForm::cancelHotelOrderCommissionFz($user->id, $diffIds, false);
                    if($res['code'] != ApiCode::CODE_SUCCESS){
                        throw new \Exception($res['msg']);
                    }
                }
            }

            $t->commit();
        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
        }

        $this->controller->commandOut("酒店订单：" . $orderData['hotel_order_id'] . "推荐分佣处理完毕");

    }
}