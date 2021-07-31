<?php

namespace app\forms\common;


use app\core\ApiCode;
use app\models\IncomeLog;
use app\models\User;
use app\plugins\commission\models\CommissionHotelPriceLog;
use app\plugins\hotel\models\Hotels;

class UserIncomeCommissionHotelForm extends UserIncomeForm
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
                ["source_type" => "hotel_commission"],
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
            CommissionHotelPriceLog::updateAll([
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
     * @param CommissionHotelPriceLog $priceLog
     * @param boolean $trans
     * @return array
     */
    public static function confirmHotelCommissionFz(User $user, $hotel_name, CommissionHotelPriceLog $priceLog, $trans = false){
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {

            $incomeLog = IncomeLog::findOne([
                "source_id"   => $priceLog->id,
                "flag"        => 0,
                "source_type" => "hotel_commission",
                "is_delete"   => 0
            ]);
            if($incomeLog){
                $desc = "来自推荐酒店“".$hotel_name."，”日期“".$priceLog->date."”订单支付结算分佣，订单ID:" . $priceLog->hotel_order_id;

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
     * @param CommissionHotelPriceLog $priceLog
     * @param false $trans
     * @return array
     */
    public static function hotelCommissionFzAdd(User $user, Hotels $hotel, CommissionHotelPriceLog $priceLog, $trans = false){
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {

            if(empty($desc)){
                $desc = "来自推荐酒店“".$hotel->name."，”日期“".$priceLog->date."”订单支付待结算分佣，订单ID:" . $priceLog->hotel_order_id;
            }

            static::change($user, $priceLog->price, self::TYPE_ADD, self::FLAG_FROZEN, "hotel_commission", $priceLog->id, $desc);

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