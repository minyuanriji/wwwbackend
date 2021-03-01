<?php
namespace app\controllers\business;
use app\models\user\User as UserModel;
use yii;
use app\models\mysql\{Goods,MemberLevel};
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


    public function getMemberLevel($goods_data){
            $data = (new MemberLevel()) -> getOneLevelData();
            if($data['upgrade_type_goods'] == 1 && $data['goods_type'] == 2){
                $goods_warehouse_data = (new Goods()) -> getGoodsData($goods_data['goods_id']);
                $data = json_decode($data['goods_warehouse_ids']);
                if(in_array($goods_warehouse_data['goods_warehouse_id'],$data)){
                    $Userlevel = (new UserModel()) -> getOneUserInfo($goods_data['user_id']);
                    if($Userlevel['level'] < 4){
                        (new UserModel()) -> updateUsers(['level' => 4],$goods_data['user_id']);
                    }
                }
            }
    }




}