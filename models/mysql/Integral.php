<?php
namespace app\models\mysql;
class Integral extends Common{
    //获取没有发送的优惠券
    public function getCouponStatus($user_id){
        return $this -> find() -> select(['id','controller_type','integral_num','period','status','user_id','finish_period']) -> where([
            'AND',
            ['user_id' => $user_id],
            ['period' => 1],
            ['controller_type' => 1],
            ['status' => 0]
        ]) -> asArray() -> all();
    }

    public function getFirstIntegral($user_id){
        return $this -> find() -> where(['user_id' => $user_id,'period_unit' => 'month']) -> one();
    }

    //更新
    public function UpdateCouponStatus($id){
        return $this -> updateAll(['status' => 2,'finish_period' => 1],['id' => $id]);
    }
}


