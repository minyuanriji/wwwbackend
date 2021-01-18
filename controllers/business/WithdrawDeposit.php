<?php
namespace app\controllers\business;
use yii\db\Query;
use app\models\user\User;
class WithdrawDeposit{
    /**
     * 打款操作金额
     * @param $data
     */
    public function getCashApply($data){
        $db = new Query;
        $price_data = $db-> select('id,user_id,type,service_fee_rate,price,status') -> from('jxmall_cash')->where("id=:id " , [':id' => $data['id']])->one();
        if($price_data['type'] == 'balance'){
            $user_data = $db-> select('id,balance,total_balance,income_frozen,income') -> from('jxmall_user')->where("id=:id " , [':id' => $price_data['user_id']])->one();
            if ($price_data['service_fee_rate'] > 0 && $price_data['service_fee_rate'] < 100) {
                $balance =  (100 - $price_data['service_fee_rate']) * $price_data['price'] / 100;
                $user_data['balance'] += $balance;
//            $user_data['income'] -= $balance;
                $user_data['total_balance'] += $balance;
                $user_data['income_frozen'] -= $price_data['price'];
                (new User()) -> updateUsers($user_data,$user_data['id']);
            }
        }

    }

}








