<?php
namespace app\controllers\business;
use app\models\OrderDetail;
use app\models\user\User as UserModel;
use yii;
use app\models\mysql\{Goods,MemberLevel,IntegralRecord};
use app\models\User;
class OrderCommon{
    /**
     * 下单记录积分
     * @param $id
     * @param $data
     * @throws yii\db\Exception
     */
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

    /**
     * 支付前查看是否允许积分卷或者购物款抵扣
     * @param $id
     * @return int
     */
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

    /**
     * 获取用户会员级别
     * @param $goods_data
     */
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

    /**
     * 退款返回抵扣卷
     * @param OrderDetail $order_detail
     */
    public function returnIntegral(OrderDetail $order_detail,$order_no){
        $user = User::findOne($order_detail->order->user_id);
        if($order_detail -> use_score === 0 && $order_detail -> integral_price > 0){
            try{
                $integral = new IntegralRecord();
                $integral -> id = null;
                $integral -> controller_type = 1;
                $integral -> mall_id = \Yii::$app->mall -> id;
                $integral -> user_id = $user -> id;
                $integral -> money = $order_detail -> integral_price;
                $integral -> desc = '售后退款单号：' . substr($order_no,-6,6);
                $integral -> before_money = $user -> static_integral;
                $integral -> type = 1;
                $integral -> expire_time = 0;
                $integral -> status = 1;
                $integral -> source_id = $order_detail -> id;
                $integral -> source_table = 'order';
                $integral -> created_at = time();
                $integral -> updated_at = time();
                $falg = $integral -> save();
                if($falg){
                    $static_integral = $user -> static_integral + $order_detail -> integral_price;
                    $user_data = (new UserModel()) -> updateUsers(['static_integral' => $static_integral],$user -> id);
                }
            }catch (\Exception $e){
            }

        }
    }




}