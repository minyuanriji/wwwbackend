<?php
namespace app\logic;
use app\helpers\SerializeHelper;
use app\models\Goods;
use app\models\Integral;
use app\models\IntegralDeduct;
use app\models\IntegralRecord;
use app\models\Order;
use app\models\User;
use app\models\OrderDetail;
use Exception;
use Yii;
use app\controllers\business\OrderCommon;
class IntegralLogic{
    /**
     * 订单取消返还购物券/积分券
     * @Author bing
     * @DateTime 2020-10-09 15:39:38
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $order
     * @return void
     */
    public function refundIntegral($order,$ctype=0){
        $trans = Yii::$app->db->beginTransaction();
        try{
            if(!empty($order)){
                if($ctype == 1){
                    //订单取消返还购物券
                    if ($order->integral_deduction_price > 0) {
                        //查询所有抵扣掉的永久购物券
                        $static_deduct_record = IntegralRecord::getDeductRecordByOrder($order,$ctype);
                        $user_id = $order->user_id;
                        // $agent = ProfitAgent::getAgentByUserId($user_id,$order->mall_id);
                        $wallet = User::getUserWallet($user_id,$order->mall_id);
                        if($static_deduct_record){
                            $record = array(
                                'controller_type'=> $ctype,
                                'mall_id'=> $static_deduct_record['mall_id'],
                                'user_id'=> $static_deduct_record['user_id'],
                                'money'=> $static_deduct_record['money'] * -1,
                                'desc'=> '订单('.$order->id.')取消,返还购物券'.($static_deduct_record['money'] * -1),
                                'before_money'=> $wallet['static_integral'],
                                'type'=> Integral::TYPE_ALWAYS,
                                'source_id'=>	$order->id,
                                'source_table'=> 'order',
                            );
                            // 写入日志
                            $res = IntegralRecord::record($record);
                            if($res === false) throw new Exception(IntegralRecord::getError());
                        }

                        //查询抵扣掉的动态积分
                        $dynamic_deduct_records = IntegralDeduct::getDeductByOrder($order);
                        
                        $before_money = $wallet['dynamic_integral'];
                        if(!empty($dynamic_deduct_records)){
                            foreach($dynamic_deduct_records as $deduct){
                                //如果当前的动态积分不是过期状态那么则返还
                                if($deduct['record']['status'] != 2){
                                    $refund = array(
                                        'mall_id'=> $deduct['mall_id'],
                                        'user_id'=> $deduct['user_id'],
                                        'source_id'=>	$deduct['source_id'],
                                        'source_table'=> 'order',
                                        'record_id' => $deduct['record_id'],
                                        'before_money' =>  $before_money,
                                        'money' => $deduct['money'] * -1,
                                        'desc' => '订单('.$order->id.')取消,返还动态购物券('.$deduct['record_id'].')面额：'.($deduct['money'] * -1)
                                    );
                                    // 写入日志
                                    $res = IntegralDeduct::deduct($refund);
                                    if($res === false) throw new Exception(IntegralDeduct::getError());
                                    if($deduct['record']['status'] == 3){
                                        $deduct['record']->status = 1;
                                        $deduct['record']->save();
                                        $res =  $deduct['record']->save();
                                        if($res === false) throw new Exception( $deduct['record']->getErrorMessage());
                                    }
                                }
                                
                            }
                        }
                    }
                }else{
                    //订单取消返还积分券
                    if ($order->score_deduction_price > 0) {
                        
                        //查询所有抵扣掉的永久积分
                        $static_deduct_record = IntegralRecord::getDeductRecordByOrder($order,$ctype);
                        $user_id = $order->user_id;
                        // $agent = ProfitAgent::getAgentByUserId($user_id,$order->mall_id);
                        $wallet = User::getUserWallet($user_id,$order->mall_id);
                        if($static_deduct_record){
                            $record = array(
                                'controller_type'=> $ctype,
                                'mall_id'=> $static_deduct_record['mall_id'],
                                'user_id'=> $static_deduct_record['user_id'],
                                'money'=> $static_deduct_record['money'] * -1,
                                'desc'=> '订单('.$order->id.')取消,返还购物券'.($static_deduct_record['money'] * -1),
                                'before_money'=> $wallet['static_score'],
                                'type'=> Integral::TYPE_ALWAYS,
                                'source_id'=>	$order->id,
                                'source_table'=> 'order',
                            );
                            
                            // 写入日志
                            $res = IntegralRecord::record($record);
                            if($res === false) throw new Exception(IntegralRecord::getError());
                        }

                        //查询抵扣掉的动态积分
                        $dynamic_deduct_records = IntegralDeduct::getDeductByOrder($order);
                        
                        $before_money = $wallet['dynamic_integral'];
                        if(!empty($dynamic_deduct_records)){
                            foreach($dynamic_deduct_records as $deduct){
                                //如果当前的动态积分不是过期状态那么则返还
                                if($deduct['record']['status'] != 2){
                                    $refund = array(
                                        'mall_id'=> $deduct['mall_id'],
                                        'user_id'=> $deduct['user_id'],
                                        'source_id'=>	$deduct['source_id'],
                                        'source_table'=> 'order',
                                        'record_id' => $deduct['record_id'],
                                        'before_money' =>  $before_money,
                                        'money' => $deduct['money'] * -1,
                                        'desc' => '订单('.$order->id.')取消,返还动态购物券('.$deduct['record_id'].')面额：'.($deduct['money'] * -1)
                                    );
                                    // 写入日志
                                    $res = IntegralDeduct::deduct($refund);
                                    if($res === false) throw new Exception(IntegralDeduct::getError());
                                    if($deduct['record']['status'] == 3){
                                        $deduct['record']->status = 1;
                                        $deduct['record']->save();
                                        $res =  $deduct['record']->save();
                                        if($res === false) throw new Exception( $deduct['record']->getErrorMessage());
                                    }
                                }
                                
                            }
                        }
                    }
                }
                
            }
            $trans->commit();
        }catch(Exception $e){
            Yii::error('取消订单返还购物券失败'.PHP_EOL. $e->getFile().'('.$e->getLine().')'.PHP_EOL."message:".$e->getMessage());
            $trans->rollBack();
            throw $e;
        }
    }


    /**
     * 升级发放积分券，购物券
     * @Author bing
     * @DateTime 2020-10-09 17:39:26
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $level_info
     * @param [type] $agent
     * @return void
     */
    public static function levelupSendIntegral($level_info,$agent,$ctype=0){
        $title = '积分券';
        if($ctype == 1){
            $title = '购物券';
        }
        try{
            echo '经销商升级赠送'.$title.PHP_EOL;
            echo '数据：'.($level_info['levelup_integral_setting'] ?? '').PHP_EOL;
            $setting = json_decode($level_info['levelup_integral_setting'] ?? '',true);
            if(empty($setting)) return false;
            $res = Integral::addIntegralPlan($agent['user_id'],$setting,'升级赠送'.$title,$ctype);
            if($res === false) throw new Exception(Integral::getError());
            return true;
        }catch(Exception $e){
            Yii::error('用户升级发放'.$title.'失败'.PHP_EOL. $e->getFile().'('.$e->getLine().')'.PHP_EOL."message:".$e->getMessage());
            return false;
        }
    }


    /**
     * 购物发放购物券
     * @Author bing
     * @DateTime 2020-10-09 17:44:13
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function shopSendIntegral($order,$type='sales'){
        $trans = Yii::$app->db->beginTransaction();
        try{
            if(!empty($order)){
                $user_id = $order->user_id;
                //Yii::error('购物券发放--123'.var_export($order->detail,true));
                foreach($order->detail as $order_detail){
                    $is_order_paid = $order_detail->goods->is_order_paid || 0;//商品订单设置支付状态
                    $order_paid = $order_detail->goods->order_paid ? SerializeHelper::decode($order_detail->goods->order_paid) : [];//商品订单设置支付参数

                    if($type == 'paid' && $is_order_paid && $order_paid['is_integral_card']){  //商品订单设置支付状态下执行
                        $goods_id = $order_detail->goods_id;
                        $integral_setting = Goods::getGooodsIntegralSetting($goods_id);
                        $integral_setting = json_decode($integral_setting,true);
                        if(empty($integral_setting)){
                            $trans->rollBack();
                            return false;
                        }
                        for($i=0;$i<$order_detail['num'];$i++){ //根据该商品购买数量循环发送
                            $res = Integral::addIntegralPlan($user_id,$integral_setting,'购买商品赠送购物券','1');
                        }
                        if($res === false) throw new Exception(Integral::getError());     
                    }elseif(!$is_order_paid){                  //商品订单不设置支付状态下执行
                        $goods_id = $order_detail->goods_id;
                        $integral_setting = Goods::getGooodsIntegralSetting($goods_id);
                        $integral_setting = json_decode($integral_setting,true);
                        if(empty($integral_setting)){
                            $trans->rollBack();
                            return false;
                        }
                        for($i=0;$i<$order_detail['num'];$i++){ //根据该商品购买数量循环发送
                            $res = Integral::addIntegralPlan($user_id,$integral_setting,'购买商品赠送购物券','1');
                        }
                        if($res === false) throw new Exception(Integral::getError());     
                    }
                               
                }
            }
            $trans->commit();
            return true;
        }catch(Exception $e){
            $trans->rollBack();
            Yii::error('用户购物发放购物券失败'.PHP_EOL. $e->getFile().'('.$e->getLine().')'.PHP_EOL."message:".$e->getMessage());
            return false;
        }
    }

    /**
     * 购物发放积分券
     * @Author bing
     * @DateTime 2020-10-09 17:44:13
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function shopSendScore($order,$type='sales'){
        $trans = Yii::$app->db->beginTransaction();
        try{
            if(!empty($order)){
                $user_id = $order->user_id;
                foreach($order->detail as $order_detail){
                    $is_order_paid = $order_detail->goods->is_order_paid || 0;//商品订单设置支付状态
                    $order_paid = $order_detail->goods->order_paid ? SerializeHelper::decode($order_detail->goods->order_paid) : [];//商品订单设置支付参数
                    if($type == 'paid' && $is_order_paid && $order_paid['is_score_card']){  //商品订单设置支付状态下执行
                        $goods_id = $order_detail->goods_id;
                        $score_setting = Goods::getGooodsScoreSetting($goods_id);
                        $score_setting = json_decode($score_setting,true);
                        
                        if(empty($score_setting)){
                            $trans->rollBack();
                            return false;
                        }
                        for($i=0;$i<$order_detail['num'];$i++){ //根据该商品购买数量循环发送
                            $res = Integral::addIntegralPlan($user_id,$score_setting,'购买商品赠送积分券','0');
                            (new OrderCommon()) -> actionOrderSales($user_id,$score_setting);
                        }
                        if($res === false) throw new Exception(Integral::getError());       
                    }elseif(!$is_order_paid){                                       //商品订单不设置支付状态下执行
                        $goods_id = $order_detail->goods_id;
                        $score_setting = Goods::getGooodsScoreSetting($goods_id);
                        $score_setting = json_decode($score_setting,true);
                        if(empty($score_setting)){
                            $trans->rollBack();
                            return false;
                        }
                        for($i=0;$i<$order_detail['num'];$i++){ //根据该商品购买数量循环发送
                            $res = Integral::addIntegralPlan($user_id,$score_setting,'购买商品赠送积分券','0');
                        }
                        if($res === false) throw new Exception(Integral::getError());       
                    }
                }
            }
            $trans->commit();
            return true;
        }catch(Exception $e){
            $trans->rollBack();
            Yii::error('用户购物发放积分券失败'.PHP_EOL. $e->getFile().'('.$e->getLine().')'.PHP_EOL."message:".$e->getMessage());
            return false;
        }
    }

    /**
     * 用户积分充值
     * @Author bing
     * @DateTime 2020-10-10 20:16:00
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $integreal_setting
     * @return void
     */
    public static function rechargeIntegral($integral_setting,$user_id,$ctype=0,$parentid=0){
        $title = '积分券';
        if($ctype==1){
            $title = '购物券';
        }
        try{
            $setting = json_decode($integral_setting,true);
            if(empty($setting)) return false;
            return Integral::addIntegralPlan($user_id,$setting,'用户充值'.$title,$ctype,$parentid);
        }catch(Exception $e){
            Yii::error('用户充值'.$title.'失败'.PHP_EOL. $e->getFile().'('.$e->getLine().')'.PHP_EOL."message:".$e->getMessage());
            throw $e;
        }
    }

    /**
     * 订单积分发放
     * @return bool
     */
    protected function giveIntegral($orderDetailList,$user)
    {
        try {
            $integral = 0;
            foreach ($orderDetailList as $orderDetail) {

                if (!in_array($orderDetail->refund_status, OrderDetail::ALLOW_ADD_SCORE_REFUND_STATUS)) {
                    continue;
                }

                if ($orderDetail->goods->give_score_type == 1) {
                    $integral += ($orderDetail->goods->give_score * $orderDetail->num);
                } else {
                    $integral += (intval($orderDetail->goods->give_score * $orderDetail->total_price / 100));
                }
            }
            if ($integral > 0) {
                \Yii::$app->currency->setUser($user)->score->add($integral, '订单购买赠送积分');
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 购物赠送积分
     * @Author bing
     * @DateTime 2020-10-09 17:44:13
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function sendScore(Order $order){
        $trans = Yii::$app->db->beginTransaction();
        try{
            if(!$order){
                throw new \Exception("订单不存在");
            }
            $orderDetails = $order->detail;
            $integral = 0;
            foreach($orderDetails as $orderDetail){

                $isScoreSend = $orderDetail->is_score_send;

                if ($orderDetail->is_score_send || !in_array($orderDetail->refund_status, OrderDetail::ALLOW_ADD_SCORE_REFUND_STATUS)) {
                    continue;
                }

                $goods = $orderDetail->goods;
                if(!$goods) continue;

                if($goods->enable_score){ //赠送积分卷
                    $scoreSetting = @json_decode($goods->score_setting,true);
                    if(empty($scoreSetting)){
                        continue;
                    }
                    if($scoreSetting['expire'] == -1 && !$order->is_confirm) //永久有效只有确认收货才送
                        continue;

                    for($i=0; $i < $orderDetail['num']; $i++){ //根据该商品购买数量循环发送
                        $res = Integral::addIntegralPlan($order->user_id, $scoreSetting,'购买商品赠送积分券','0');
                        if(!$res){
                            throw new \Exception(Integral::getError());
                        }
                    }

                    $isScoreSend = 1;

                }else{ //赠送积分
                    if($order->is_confirm){
                        if ($orderDetail->goods->give_score_type == 1) {
                            $integral += ($orderDetail->goods->give_score * $orderDetail->num);
                        } else {
                            $integral += (intval($orderDetail->goods->give_score * $orderDetail->total_price / 100));
                        }
                        $isScoreSend = 1;
                    }
                }

                if($isScoreSend){
                    $orderDetail->is_score_send = 1;
                    if(!$orderDetail->save()){
                        throw new \Exception("积分赠送状态更新失败");
                    }
                }
            }

            if ($integral > 0) {
                \Yii::$app->currency->setUser($order->user)->score->add($integral, '订单购买赠送积分');
            }



            $trans->commit();
            return true;
        }catch(Exception $e){
            $trans->rollBack();
            Yii::error('用户购物发放积分失败'.PHP_EOL. $e->getFile().'('.$e->getLine().')'.PHP_EOL."message:".$e->getMessage());
            return false;
        }
    }
}