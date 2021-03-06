<?php
namespace app\controllers\business;
use app\models\mysql\{Integral,IntegralRecord};
use app\models\User;
use app\models\user\User as userMode;
class NewUserIntegral {
    /**
     * 用户第一次注册发送积分
     * @param $user_id
     */
    public function SendUserIntegral($user_id){
        $transaction = \Yii::$app->db->beginTransaction(); //开启事务
        try{
            $integral_data = (new Integral()) -> getFirstIntegral($user_id);
            if(empty($integral_data)){
                $user_data = User::findOne($user_id);
                $integral = new Integral();
                $integral -> id = null;
                $integral -> controller_type = 0;
                $integral -> mall_id = \Yii::$app->mall->id;
                $integral -> user_id = $user_id;
                $integral -> integral_num = 300;
                $integral -> period = 12;
                $integral -> period_unit = 'month';
                $integral -> finish_period = 1;
                $integral -> type = 2;
                $integral -> status = 1;
                $integral -> effective_days = 30;
                $integral -> next_publish_time = strtotime(date('Y-m-01',strtotime('+ 1 month')));
                $integral -> desc = '新用户赠送积分';
                $integral -> created_at = time();
                $integral -> updated_at = time();
                $integral -> parent_id = 0;
                $integral -> save();


                $date = date('Y-m-d',time());
                $day = date("t",strtotime($date));
                $IntegralRecord = new IntegralRecord();
                $IntegralRecord -> id = null;
                $IntegralRecord -> controller_type = 0;
                $IntegralRecord -> mall_id = \Yii::$app->mall->id;
                $IntegralRecord -> user_id = $user_id;
                $IntegralRecord -> money = 300;
                $IntegralRecord -> desc = '用户充值积分券 发放进度(1/12)';
                $IntegralRecord -> before_money = $user_data -> score;
                $IntegralRecord -> type = 2;
                $IntegralRecord -> expire_time = strtotime('+'. $day .'days',strtotime(date('Y-m-01'))) - 1;
                $IntegralRecord -> status = 1;
                $IntegralRecord -> source_id = $integral -> attributes['id'];
                $IntegralRecord -> source_table = 'integral';
                $IntegralRecord -> created_at = time();
                $IntegralRecord -> updated_at = time();
                $IntegralRecord -> save();

                (new userMode()) -> updateUsers(['dynamic_integral' => ($user_data -> dynamic_integral + 300),'score' => ($user_data -> score + 300)],$user_data -> id);
                $transaction -> commit();
                return 1;
            }
            return 2;
        }catch (\Exception $e){
            $transaction ->rollBack();
            return 0;
        }
    }

}





