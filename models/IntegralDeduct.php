<?php
namespace app\models;

use Exception;
use Yii;

class IntegralDeduct extends BaseActiveRecord{

    
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%integral_deduct}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['user_id','money'], 'required'],
            [['mall_id','user_id','record_id','source_id','created_at', 'updated_at'], 'integer'],
            [['money','before_money','controller_type'], 'number'],
            ['source_table','string'],
            [['desc'],'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(){
        return [
            'id'=>'ID',
            'controller_type'=>'卡券类型',
            'mall_id'=>'商城ID',
            'user_id'=>'用户ID',
            'money'=>'资金变动(支持负数)',
            'record_id'=>'动态积分记录ID',
            'desc'=>'说明',
            'before_money'=>'变动前的金额',
            'source_id'=>	'来源ID',
            'source_table'=> '来源表',
            'created_at'=>'创建时间',
            'updated_at'=>'更新时间'
        ];
    }

    public function getUser(){
        return $this->hasOne(User::class,['id'=>'user_id']);
    }

    public function getRecord(){
        return $this->hasOne(IntegralRecord::class,['id'=>'record_id']);
    }
    /**
     * 新增记录
     * @Author bing
     * @DateTime 2020-09-22 11:21:42
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param array $log
     * @return void
     */
    public static function deduct(array $log,$ctype=0){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $model = new self();
            $model->loadDefaultValues();
            $model->attributes = $log;
            $res = $model->save();
            if($res === false) throw new Exception($model->getErrorMessage());
            //找用户钱包
            $wallet = User::getUserWallet($log['user_id']);
            if($ctype == 1){
                //修改经销商的购物卡券动态金额
                $wallet->dynamic_integral += $log['money'];
            }else{
                //修改经销商的积分卡券动态金额
                $wallet->score         += $log['money'];
                $wallet->dynamic_score = $wallet->score;
                $wallet->total_score   = $wallet->dynamic_score + $wallet->static_score;

            }
            $res = $wallet->save(false);
            if($res === false) throw new Exception($wallet->getErrorMessage());
            $transaction->commit();
            return true;
        }catch(\Exception $e){
            $transaction->rollBack();
            self::$error = $e->getMessage();
            return false;
        }
    }

    
    /**
     * 统计动态积分抵扣总额
     * @Author bing
     * @DateTime 2020-10-08 09:19:19
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function countIntegralDeduct($record_id){
        return self::find()
        ->where(array('=','record_id',$record_id))
        ->sum('money');
    }

    /**
     * 购物积分券抵扣
     * @DateTime 2020-10-08 14:14:26
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return boolean
     */
    public static function buyGooodsScoreDeduct(Order $order){
        try{
            $wallet = User::getUserWallet($order->user_id, $order->mall_id);

            //查询用户可用积分券按过期时间升序排列
            $can_use_integrals = IntegralRecord::getIntegralAscExpireTime($order->user_id, 0);

            //要抵扣的数值
            $integral_deduction_price = $order->score_deduction_price;
            if($integral_deduction_price > 0){
                if($wallet['score'] > 0){
                    //有动态积分优先扣减
                    $deduct = array(
                        'controller_type' => 0,
                        'mall_id'         => $order['mall_id'],
                        'user_id'         => $order['user_id'],
                        'source_id'       => $order->id,
                        'source_table'    => 'order',
                    );

                    $before_money = $wallet['score'];

                    //动态积分足够扣减
                    foreach($can_use_integrals as $integral){
                        $deduct['record_id']    = $integral['id'];
                        $deduct['before_money'] = $before_money;
                        $can_deduct_money = !empty($integral['deduct']) ? $integral['money'] + array_sum(array_column($integral['deduct'], 'money')) : $integral['money'];

                        if(intval(bcmul($can_deduct_money,100) >= intval(bcmul($integral_deduction_price,100)))){
                            //当前券的面值足够抵扣掉订单，则从此券中扣除
                            $deduct['money'] = $integral_deduction_price * -1;
                            $deduct['desc']  = '订单('.$order->id.')创建扣除动态积分券('.$integral['id'].')抵扣：'.$integral_deduction_price;
                            if(!self::deduct($deduct,0)){
                                throw new Exception(self::getError());
                            }

                            if(intval(bcmul($can_deduct_money,100) == intval(bcmul($integral_deduction_price,100)))){
                                $integral->status = 3;
                                if(!$integral->save()){
                                    throw new Exception($integral->getErrorMessage());
                                }
                            }

                            $before_money -= $integral_deduction_price;
                            $integral_deduction_price = 0;
                            break;
                        }else{
                            //当前券的面值不足够抵扣掉订单使用的券，则扣除当前全部面值
                            $integral_deduction_price -= $can_deduct_money;
                            $deduct['money'] = $can_deduct_money * -1;
                            $deduct['desc']  = '订单('.$order->id.')创建扣除动态积分券('.$integral['id'].')抵扣：'. $can_deduct_money;
                            $before_money -= $integral_deduction_price;
                            if(!self::deduct($deduct, 0)){
                                throw new Exception(self::getError());
                            }
                            $integral->status = 3;
                            if(!$integral->save()){
                                throw new Exception($integral->getErrorMessage());
                            }
                        }
                    }

                    //使用永久积分补足不够的
                    if($integral_deduction_price > 0){
                        self::_deductStaticScore($wallet, $integral_deduction_price, $order, 0);
                    }
                }else{
                    self::_deductStaticScore($wallet, $integral_deduction_price, $order,0);
                }
            }
        }catch (\Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 购物红包券、积分券抵扣
     * @Author bing
     * @DateTime 2020-10-08 14:14:26
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return boolean
     */
    public static function buyGooodsDeduct($order,$ctype=0){
        try{
            $user_id = $order->user_id;
            $wallet = User::getUserWallet($user_id,$order->mall_id);
            //查询用户可用红包券,并且按过期时间升序排列
            $can_use_integrals = IntegralRecord::getIntegralAscExpireTime($user_id,$ctype);
            $integral_deduction_price = $ctype==1?$order->integral_deduction_price:$order->score_deduction_price; // 订单抵扣红包券或积分
            if($integral_deduction_price > 0){
                if($ctype==1){
                    if($wallet['dynamic_integral'] > 0){
                        //有动态红包券优先扣减
                        $deduct = array(
                            'controller_type'=> $ctype,
                            'mall_id'=> $order['mall_id'],
                            'user_id'=> $order['user_id'],
                            'source_id'=>	$order->id,
                            'source_table'=> 'order',
                        );
                        $before_money = $wallet['dynamic_integral'];
                        //动态红包券足够扣减
                        foreach($can_use_integrals as $integral){
                            $deduct['record_id'] = $integral['id'];
                            $deduct['before_money'] =  $before_money;
                            $can_deduct_money = !empty($integral['deduct']) ? $integral['money'] + array_sum(array_column($integral['deduct'],'money')) : $integral['money'];
                            
                            if(intval(bcmul($can_deduct_money,100) >= intval(bcmul($integral_deduction_price,100)))){
                                //当前券的面值足够抵扣掉订单，则从此券中扣除
                                $deduct['money'] = $integral_deduction_price * -1;
                                $deduct['desc'] = '订单('.$order->id.')创建扣除动态红包券('.$integral['id'].')抵扣：'.$integral_deduction_price;
                                $res = self::deduct($deduct,$ctype);
                                if($res === false) throw new Exception(self::getError());
                                if(intval(bcmul($can_deduct_money,100) == intval(bcmul($integral_deduction_price,100)))){
                                    $integral->status = 3;
                                    $res = $integral->save();
                                    if($res === false) throw new Exception($integral->getErrorMessage());
                                }
                                $before_money -= $integral_deduction_price;
                                $integral_deduction_price = 0;
                                break;
                            }else{
                                //当前券的面值不足够抵扣掉订单使用的券，则扣除当前全部面值
                                $integral_deduction_price -= $can_deduct_money;
                                $deduct['money'] = $can_deduct_money * -1;
                                $deduct['desc'] = '订单('.$order->id.')创建扣除动态红包券('.$integral['id'].')抵扣：'. $can_deduct_money;
                                $before_money -= $integral_deduction_price;
                                $res = self::deduct($deduct,$ctype);
                                if($res === false) throw new Exception(self::getError());
                                $integral->status = 3;
                                $res = $integral->save();
                                if($res === false) throw new Exception($integral->getErrorMessage());
                            }
                        }
                        //使用永久红包券补足不够的
                        if($integral_deduction_price > 0){
                            self::_deductStaticIntegral($wallet,$integral_deduction_price,$order,$ctype);
                        }
                        //使用永久红包券补足不够的
                        // if($integral_deduction_price > 0){
                        //     if($ctype==1){
                        //         self::_deductStaticIntegral($wallet,$integral_deduction_price,$order,$ctype);
                        //     }else{
                        //         self::_deductStaticScore($wallet,$integral_deduction_price,$order,$ctype);
                        //     }
                        // }
                    }else{
                        // if($ctype==1){
                        //     self::_deductStaticIntegral($wallet,$integral_deduction_price,$order,$ctype);
                        // }else{
                        //     self::_deductStaticScore($wallet,$integral_deduction_price,$order,$ctype);
                        // }
                        self::_deductStaticIntegral($wallet,$integral_deduction_price,$order,$ctype);
                    }
                }else{
                    
                    if($wallet['dynamic_score'] > 0){
                        
                        //有动态积分优先扣减
                        $deduct = array(
                            'controller_type'=> $ctype,
                            'mall_id'=> $order['mall_id'],
                            'user_id'=> $order['user_id'],
                            'source_id'=>	$order->id,
                            'source_table'=> 'order',
                        );
                        
                        $before_money = $wallet['dynamic_score'];
                        $user_score_desc = '';
                        //动态积分足够扣减
                        foreach($can_use_integrals as $integral){
                            $deduct['record_id'] = $integral['id'];
                            $deduct['before_money'] =  $before_money;
                            $can_deduct_money = !empty($integral['deduct']) ? $integral['money'] + array_sum(array_column($integral['deduct'],'money')) : $integral['money'];
                            
                            if(intval(bcmul($can_deduct_money,100) >= intval(bcmul($integral_deduction_price,100)))){
                                //当前券的面值足够抵扣掉订单，则从此券中扣除
                                $deduct['money'] = $integral_deduction_price * -1;
                                $deduct['desc'] = '订单('.$order->id.')创建扣除动态积分券('.$integral['id'].')抵扣：'.$integral_deduction_price;
                                $res = self::deduct($deduct,$ctype);
                                if($res === false) throw new Exception(self::getError());
                                if(intval(bcmul($can_deduct_money,100) == intval(bcmul($integral_deduction_price,100)))){
                                    $integral->status = 3;
                                    $res = $integral->save();
                                    if($res === false) throw new Exception($integral->getErrorMessage());
                                }
                                $before_money -= $integral_deduction_price;
                                $integral_deduction_price = 0;
                                break;
                            }else{
                                //当前券的面值不足够抵扣掉订单使用的券，则扣除当前全部面值
                                $integral_deduction_price -= $can_deduct_money;
                                $deduct['money'] = $can_deduct_money * -1;
                                $deduct['desc'] = '订单('.$order->id.')创建扣除动态积分券('.$integral['id'].')抵扣：'. $can_deduct_money;
                                $before_money -= $integral_deduction_price;
                                $res = self::deduct($deduct,$ctype);
                                if($res === false) throw new Exception(self::getError());
                                $integral->status = 3;
                                $res = $integral->save();
                                if($res === false) throw new Exception($integral->getErrorMessage());
                            }
                        }
                        
                        //使用永久积分补足不够的
                        if($integral_deduction_price > 0){
                            if($ctype==1){
                                self::_deductStaticIntegral($wallet,$integral_deduction_price,$order,$ctype);
                            }else{
                                self::_deductStaticScore($wallet,$integral_deduction_price,$order,$ctype);
                            }
                        }
                    }else{
                        if($ctype==1){
                            self::_deductStaticIntegral($wallet,$integral_deduction_price,$order,$ctype);
                        }else{
                            self::_deductStaticScore($wallet,$integral_deduction_price,$order,$ctype);
                        }
                    }
                }

                
            }
            return true;
        }catch(\Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
        
    }

    /**
     * 使用永久红包券抵扣
     * @Author bing
     * @DateTime 2020-10-09 15:50:03
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $integral_deduction_price
     * @param [type] $order
     * @return void
     */
    private static function _deductStaticIntegral($wallet,$integral_deduction_price,$order,$ctype){
        
        // 使用永久红包券抵扣
        $diff_integral = $wallet['static_integral'] - $integral_deduction_price;
        if($diff_integral < 0) throw new Exception('永久红包券不足');
        $record = array(
            'controller_type'=> $ctype,
            'mall_id'=> $order['mall_id'],
            'user_id'=> $order['user_id'],
            'money'=> $integral_deduction_price * -1,
            'desc'=> '订单('.$order->id.')创建,扣除红包券'.$integral_deduction_price,
            'before_money'=> $wallet['static_integral'],
            'type'=> Integral::TYPE_ALWAYS,
            'source_id'=>	$order->id,
            'source_table'=> 'order',
        );
        // 写入日志
        $res = IntegralRecord::record($record);
        if($res === false) throw new Exception(IntegralRecord::getError());
    }

    /**
     * 使用永久积分抵扣
     * @Author bing
     * @DateTime 2020-10-09 15:50:03
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $integral_deduction_price
     * @param [type] $order
     * @return void
     */
    private static function _deductStaticScore($wallet, $integral_deduction_price, $order, $ctype){
        
        if($integral_deduction_price >= $wallet['static_score'] ){
            $kouchu = $wallet['static_score'] * -1;
        }else{
            $kouchu = $integral_deduction_price * -1;
        }
        $record = array(
            'controller_type' => $ctype,
            'mall_id'         => $order['mall_id'],
            'user_id'         => $order['user_id'],
            'money'           => $kouchu,
            'desc'            => '订单('.$order->id.')创建,扣除积分' . $kouchu,
            'before_money'    => $wallet['static_score'],
            'type'            => Integral::TYPE_ALWAYS,
            'source_id'       => $order->id,
            'source_table'    => 'order',
        );

        // 写入日志
        if(!IntegralRecord::record($record)){
            throw new Exception(IntegralRecord::getError());
        }
    }

    /**
     * 获取抵扣掉的动态积分
     * @Author bing
     * @DateTime 2020-10-09 16:28:32
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $order
     * @return array
     */
    public static function getDeductByOrder($order){
        return self::find()
        ->where(array('user_id'=>$order['user_id'],'source_table'=>'order','source_id'=>$order['id']))
        ->andWhere(array('<','money',0))
        ->with(['record'])
        ->all();
    }
}
