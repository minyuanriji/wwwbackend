<?php

namespace app\forms\common;

use app\core\ApiCode;
use app\models\IncomeLog;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\commission\models\CommissionAddcreditPriceLog;

class UserIncomeCommissionAddcreditForm extends UserIncomeForm
{
    /**
     * 取消话费订单待结算收益
     * @param int $user_id 用户ID
     * @param array $source_ids 分佣记录ID
     * @param bool $trans
     */
    public static function cancelAddcreditOrderCommissionFz($user_id, $source_ids, $trans = false)
    {
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
                "is_delete" => 1,
                "deleted_at" => time()
            ], $andWhere);

            //用户待收益扣除
            if ($user_id) {
                User::updateAllCounters([
                    "total_income" => -1 * $sumPrice,
                    "income_frozen" => -1 * $sumPrice
                ], ["id" => $user_id]);
            }

            //取消分佣记录
            CommissionHotelPriceLog::updateAll([
                "status" => -1,
                "updated_at" => time()
            ], "id IN (" . implode(",", $source_ids) . ")");

            $trans && $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $e) {
            $trans && $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }

    }

    /**
     * 新增话费订单结算分佣
     * @param User $user
     * @param AddcreditOrder $addcreditOrder
     * @param CommissionAddcreditPriceLog $priceLog
     * @param false $trans
     * @return array
     */
    public static function AddcreditCommissionFzAdd(User $user, AddcreditOrder $addcreditOrder, CommissionAddcreditPriceLog $priceLog, $trans = false)
    {
        $trans && ($t = \Yii::$app->db->beginTransaction());
        try {

            if (empty($desc)) {
                $desc = "来自话费充值“" . $addcreditOrder->mobile . "，”金额：“" . $addcreditOrder->order_price . "”订单支付结算分佣，订单ID:" . $addcreditOrder->id;
            }

            static::change($user, $priceLog->price, self::TYPE_ADD, self::FLAG_INCOME, "addcredit", $priceLog->id, $desc);

            $trans && $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '操作成功'
            ];
        } catch (\Exception $e) {
            $trans && $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}