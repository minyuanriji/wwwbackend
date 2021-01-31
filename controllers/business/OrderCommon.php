<?php
namespace app\controllers\business;
use yii;
use app\models\mysql\{Goods};
use app\models\user\User;
class OrderCommon{
        public function actionOrderSales($id,$data){
        $db = yii::$app->db;
        $transaction = Yii::$app->db->beginTransaction();
        $sql = "select score from jxmall_user where id = {$id} ";
        $res = $db -> createCommand($sql) -> queryOne();
        $mall_id = yii::$app->mall->id;
        try{
            $total_score = $res['score'] + $data['integral_num'];
            $aa = $db -> createCommand() -> insert('jxmall_score_log',[
                    'mall_id' => $mall_id,
                    'user_id' => $id,
                    'type' => 1,
                    'score' => $data['integral_num'],
                    'current_score' => $total_score,
                    'desc' => '订单购买赠送积分',
                    'custom_desc' => '{"msg":"用户积分变动说明"}',
                    'created_at' => time()
            ]) -> execute();
            $transaction -> commit();
        }catch (\Exception $e){
            $transaction ->rollBack();
        }
    }
    
    
    public function getOneNavData($id){
        try{
            $result = (new Goods()) -> getOneNavData($id);
            if($result['forehead_score'] !== '0.00' && $result['max_deduct_integral'] !== '0'){
                $flag = 0;
            }else if($result['forehead_score'] !== '0.00'){
                $flag = 1;
            }else if($result['max_deduct_integral'] !== '0'){
                $flag = 2;
            }else{
                $flag = 3;
            }
        }catch (\Exception $e){
            $flag = 0;
        }
        return $flag;
    }




}