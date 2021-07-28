<?php

namespace app\forms\common;


use app\core\ApiCode;
use app\models\IncomeLog;
use app\models\User;
use app\plugins\commission\models\CommissionHotel3rPriceLog;
use app\plugins\hotel\models\Hotels;

class UserIncomeCommissionHotel3rForm extends UserIncomeForm
{
    /**
     * 取消推荐酒店订单待结算收益
     * @param int $user_id 用户ID
     * @param array $source_ids 分佣记录ID
     * @param bool $trans
     */
    public static function cancelHotelOrderCommissionFz($user_id, $source_ids, $trans = false){
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {
            //删除待结算收益记录
            $andWhere = [
                "AND",
                ["IN", "source_id", $source_ids],
                ["source_type" => "hotel_3r_commission"],
                ["flag" => 0],
                ["is_delete" => 0],
                ["type" => 1]
            ];
            $sumPrice = (float)IncomeLog::find()->andWhere($andWhere)->sum("income");
            IncomeLog::updateAll([
                "is_delete"  => 1,
                "deleted_at" => time()
            ], $andWhere);

            //用户待收益扣除
            if($user_id){
                User::updateAllCounters([
                    "total_income"  => -1 * $sumPrice,
                    "income_frozen" => -1 * $sumPrice
                ], ["id" => $user_id]);
            }

            //取消分佣记录
            CommissionHotel3rPriceLog::updateAll([
                "status" => -1,
                "updated_at" => time()
            ], "id IN (".implode(",", $source_ids).")");

            $trans && $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans && $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    /**
     * 确认推荐酒店订单待结算
     * @param User $user
     * @param string $hotel_name
     * @param CommissionHotel3rPriceLog $priceLog
     * @param boolean $trans
     * @return array
     */
    public static function confirmHotelCommissionFz(User $user, $hotel_name, CommissionHotel3rPriceLog $priceLog, $trans = false){
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {

            $incomeLog = IncomeLog::findOne([
                "source_id"   => $priceLog->id,
                "flag"        => 0,
                "source_type" => "hotel_3r_commission",
                "is_delete"   => 0
            ]);
            if($incomeLog){

                $desc = "来自推荐用户酒店“".$hotel_name."，”日期“".$priceLog->date."”订单支付结算分佣，订单ID:" . $priceLog->hotel_order_id;

                $incomeLog->desc = $desc;
                $incomeLog->flag = 1;
                $incomeLog->updated_at = time();
                if(!$incomeLog->save()){
                    throw new \Exception(json_encode($incomeLog->getErrors()));
                }
                $user->income += $incomeLog->income;
                $user->income_frozen -= $incomeLog->income;
                if(!$user->save()){
                    throw new \Exception(json_encode($incomeLog->getErrors()));
                }
            }

            $priceLog->status = 1;
            $priceLog->updated_at = time();
            if(!$priceLog->save()){
                throw new \Exception(json_encode($priceLog->getErrors()));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans && $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    /**
     * 新增推荐酒店订单待结算分佣
     * @param User $user
     * @param Hotels $hotel
     * @param CommissionHotel3rPriceLog $priceLog
     * @param false $trans
     * @return array
     */
    public static function hotelCommissionFzAdd(User $user, Hotels $hotel, CommissionHotel3rPriceLog $priceLog, $trans = false){
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {

            $desc = "来自推荐用户酒店“".$hotel->name."，”日期“".$priceLog->date."”订单支付结算分佣，订单ID:" . $priceLog->hotel_order_id;

            static::change($user, $priceLog->price, self::TYPE_ADD, self::FLAG_FROZEN, "hotel_3r_commission", $priceLog->id, $desc);

            $trans && $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans && $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}