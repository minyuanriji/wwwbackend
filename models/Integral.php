<?php
namespace app\models;

use app\models\Mall;
use app\models\User;
use app\plugins\agent\models\Agent;
use Exception;
use Yii;

class Integral extends BaseActiveRecord
{
    const UNIT_WEEK = 'week';
    const UNIT_MONTH = 'month';

    const STATUS_WAIT = 0;
    const STATUS_DOING = 1;
    const STATUS_FINISH = 2;

    const TYPE_ALWAYS = 1;//永久积分
    const TYPE_DYNAMIC = 2;//动态积分

    public static $status_list = array(
        self::STATUS_FINISH => '发放完',
        self::STATUS_DOING => '发放中',
        self::STATUS_WAIT => '待发放',
    );

    public static $unit_list = array(
        self::UNIT_WEEK => '周',
        self::UNIT_MONTH => '月'
    );

    public static $type_list = array(
        self::TYPE_ALWAYS => '永久卡券',
        self::TYPE_DYNAMIC => '限时卡券'
    );

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%integral}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id','user_id','period'], 'required'],
            [['mall_id','user_id','controller_type','parent_id','period','next_publish_time','finish_period','effective_days','created_at', 'updated_at'], 'integer'],
            [['integral_num'], 'number'],
            ['status','in','range'=>[self::STATUS_WAIT,self::STATUS_DOING,self::STATUS_FINISH]],
            ['period_unit','in','range'=>[self::UNIT_MONTH,self::UNIT_WEEK]],
            ['type','in','range'=>[self::TYPE_ALWAYS,self::TYPE_DYNAMIC]],
            [['source_id', 'source_type'], 'safe']

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'controller_type'=>'卡券类型',
            'mall_id' => '商城ID',
            'user_id' => '用户ID',
            'integral_num' =>'积分面值',
            'period' =>'周期',
            'period_unit' =>'周期',
            'finish_period' =>'已完成周期数',
            'status' =>'状态',
            'next_publish_time' =>'下次发放时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'parent_id'=>'卡券发放人'
        ];
    }

    public function getUser(){
        return $this->hasOne(User::class,['id'=>'user_id']);
    }


    /**
     * 添加一条执行计划
     * @Author bing
     * @DateTime 2020-10-07 18:29:39
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param int $user_id
     * @param int $integral_num
     * @param int $period
     * @param string $period_unit
     * @return void
     */
    public static function addIntegralPlan($user_id,$integral_setting,$desc='',$ctype=0,$parentid=0, $mall_id = 0){

        try{
            $model = new self();
            $model->loadDefaultValues();
            $model->controller_type   = $ctype;
            $model->user_id           = $user_id;
            $model->mall_id           = $mall_id ? $mall_id : Yii::$app->mall->id;
            $model->parent_id         = $parentid;
            $model->integral_num      = $integral_setting['integral_num'];
            $model->period            = $integral_setting['period'];
            $model->period_unit       = !empty($integral_setting['period_unit']) ? $integral_setting['period_unit'] : "month";
            $model->effective_days    = $integral_setting['expire'] == -1 ? 0 : $integral_setting['expire'];
            $model->next_publish_time = time();
            $model->desc              = $desc;
            $model->type              = $integral_setting['expire'] == -1 ? self::TYPE_ALWAYS : self::TYPE_DYNAMIC;

            if(!empty($integral_setting['source_type'])){
                $model->source_type = $integral_setting['source_type'];
                $model->source_id = !empty($integral_setting['source_id']) ? $integral_setting['source_id'] : 0;
            }

            if(!$model->save()){
                throw new Exception($model->getErrorMessage());
            }
            return true;
        }catch(Exception $e){
            self::$error = $e->getMessage();
            return false;
        }

    }

    /**
     * 派发积分/金豆券
     * @Author bing
     * @DateTime 2020-10-07 18:44:27
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function sendIntegral(){

        //获取计划执行时间小于当前时间，状态未结束的计划
        $now = time();

        $whereStr = "(period > finish_period)"; //周期数未达到
        $whereStr.= " AND `status` IN (0, 1)"; //状态为未发放，发放中
        $whereStr.= " AND next_publish_time<'".time()."'";
        $query = static::find()->where($whereStr)->limit(5);
        $planList = $query->orderBy([
            'finish_period'     => 'ASC',
            'next_publish_time' => 'ASC',
            'updated_at'        => 'ASC'
        ])->all();

        if(!empty($planList)){
            $transaction = Yii::$app->db->beginTransaction();
            try{
                foreach($planList as $plan){
                    if($plan['controller_type'] == 0 && $plan['period_unit'] == 'month'){
                        if($now < $plan['next_publish_time']){
                            continue;
                        }
                    }
                    Yii::$app->mall = Mall::findOne(array('id'=>$plan['mall_id']));
                    $wallet = User::getUserWallet($plan['user_id'], $plan['mall_id']);
                    $finish_period = $plan['finish_period'] + 1;
                    $desc = $plan['desc'] .' 发放进度('.$finish_period.'/'.$plan['period'].')';
                    if($plan['controller_type'] == 1){
                        $before_money = $plan['type'] == self::TYPE_ALWAYS ? $wallet['static_integral'] : $wallet['dynamic_integral'];
                    }else{
                        $before_money = $plan['type'] == self::TYPE_ALWAYS ? $wallet['static_score'] : $wallet['dynamic_score'];
                    }
                    // 按充值日期过期
//                $expire_time = $plan['type'] == self::TYPE_ALWAYS ? 0 : strtotime('+'.$plan['effective_days'].'days',strtotime(date('Y-m-01')));

                    // 按每个月的1号 凌晨12点失效
                    if($plan['effective_days'] >= 30){
                        $date = date('Y-m-d',time());
                        $day = date("t",strtotime($date));
                        $expire_time = $plan['type'] == self::TYPE_ALWAYS ? 0 : strtotime('+'. $day .'days',strtotime(date('Y-m-01'))) - 1;
//                    \Yii::$app->redis -> set('key1',date('m'));
//                    $expire_time = $expire_time - 10;
                    }else{
                        $expire_time = $plan['type'] == self::TYPE_ALWAYS ? 0 : strtotime('+'.$plan['effective_days'].'days',strtotime(date('Y-m-01'))) - 1;
                    }

                    // 测试
                    // $expire_time = $plan['type'] == self::TYPE_ALWAYS ? 0 : strtotime('+ 30 minutes',$now);

                    //修改当前计划执行情况
                    $plan->finish_period = $finish_period;
                    if($finish_period == $plan['period']){
                        $plan->status = self::STATUS_FINISH;
                    }else{
                        $plan->status =  self::STATUS_DOING;
                        //计算下期的发放时间
                        switch($plan['period_unit']){
                            case 'week':
                                $plan->next_publish_time = strtotime('+ 1 week',$now);
                                break;
                            case 'month':
                                //获取每次充卡开始时间 到 满一个月发放时间
//                           $plan->next_publish_time = strtotime('+ 1 month',$now);

                                //每个月1号开始发送积分
                                $plan->next_publish_time = strtotime(date('Y-m-01',strtotime('+ 1 month')));
                                //测试
//                             $plan->next_publish_time = strtotime('+ 2 minutes',$now);
                                break;
                        }
                    }

                    if(!$plan->save()) {
                        throw new Exception($plan->getErrorMessage());
                    }

                    $record = array(
                        'controller_type' => $plan['controller_type'],
                        'mall_id'         => $plan['mall_id'],
                        'user_id'         => $plan['user_id'],
                        'money'           => $plan['integral_num'],
                        'desc'            => $desc,
                        'before_money'    => $before_money,
                        'type'            => $plan['type'],
                        'expire_time'     => $expire_time,
                        'status'          => 1,
                        'source_id'       => $plan->id,
                        'source_table'    => 'integral'
                    );

                    // 写入日志
                    $flag = User::getOneUserFlag($plan['user_id']);
                    if(!empty($flag)){
                        if(!IntegralRecord::record($record,$plan['parent_id'], false)){
                            throw new Exception(IntegralRecord::getError());
                        }
                    }
                }


                $transaction->commit();

            }catch(\Exception $e){
                $transaction->rollBack();
                \Yii::$app->redis -> set('show1',$e->getMessage());
                self::$error = $e->getMessage();
                return false;
            }
        }

        return true;
    }

}
