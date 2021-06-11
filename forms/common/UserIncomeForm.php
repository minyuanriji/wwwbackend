<?php
namespace app\forms\common;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Cash;
use app\models\IncomeLog;
use app\models\User;

class UserIncomeForm extends BaseModel{

    const FLAG_FROZEN   = 0;
    const FLAG_INCOME   = 1;

    const TYPE_ADD      = 1;
    const TYPE_SUB      = 2;

    /**
     * 股东分红
     * @param User $user
     * @param $price
     * @param string $remark
     * @return void
     */
    public static function bossAdd(User $user, $price, $source_id, $desc = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {

            if(empty($desc)){
                $desc = "来自股东分红[ID:{$source_id}]";
            }

            static::change($user, $price, self::TYPE_ADD, self::FLAG_INCOME, "boss", $source_id, $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

        /**
     * 管理员充值
     * @param User $user
     * @param $price
     * @param string $remark
     * @return void
     */
    public static function adminAdd(User $user, $price, $source_id, $remark = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$source_id}]充值：" . $remark;
            static::change($user, $price, self::TYPE_ADD, self::FLAG_INCOME, "admin", time(), $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 管理员扣减
     * @param User $user
     * @param $price
     * @param string $remark
     * @return void
     */
    public static function adminSub(User $user, $price, $source_id, $remark = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$source_id}]扣减：" . $remark;

            static::change($user, $price, self::TYPE_SUB, self::FLAG_INCOME, "admin", time(), $desc);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 拒绝提现加收入
     * @param User $user
     * @param $price
     * @param $source_type
     * @param $source_id
     */
    public static function cashReject(User $user, Cash $cash){

        $t = \Yii::$app->db->beginTransaction();
        try {

            static::change($user, $cash->price, self::TYPE_ADD, self::FLAG_INCOME, "cash", $cash->id);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 申请提现减收入
     * @param User $user
     * @param $price
     * @param $source_type
     * @param $source_id
     */
    public static function cashSub(User $user, $price, $source_id){

        $t = \Yii::$app->db->beginTransaction();
        try {

            static::change($user, $price, self::TYPE_SUB, self::FLAG_INCOME, "cash", $source_id);

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

    private static function change(User $user, $price, $type, $flag, $source_type, $source_id, $desc = null){

        $totalIncome = floatval($user->total_income);

        if($type == 1){ //收入
            $user->total_income = $totalIncome + $price;
            if($flag == 0){ //冻结
                $user->income_frozen = floatval($user->income_frozen) + $price;
            }else{ //结算
                $user->income = floatval($user->income) + $price;
            }
        }else{ //支出
            $user->total_income = max(0, $totalIncome - floatval($price));
            if($flag == 0){ //冻结
                $user->income_frozen = max(0, floatval($user->income_frozen) - floatval($price));
            }else{ //结算
                $user->income = max(0, floatval($user->income) - floatval($price));
            }
        }

        if(!$user->save()){
            throw new \Exception(json_encode($user->getErrors()));
        }


        if($desc === null){
            $desc = "";
            if($source_type == "cash"){
                if($type == 2){ //申请支出
                    $desc = "提现申请[ID:".$source_id."]收入扣减";
                }else{ //提现失败返还
                    $desc = "提现失败[ID:".$source_id."]收入返还";
                }
            }
        }

        $incomeLog = new IncomeLog([
            "mall_id"     => $user->mall_id,
            "user_id"     => $user->id,
            "type"        => $type,
            "money"       => $totalIncome,
            "income"      => floatval($price),
            "desc"        => $desc,
            "flag"        => $flag,
            "source_id"   => $source_id,
            "source_type" => $source_type,
            "created_at"  => time(),
            "updated_at"  => time()
        ]);
        if(!$incomeLog->save()){
            throw new \Exception(json_encode($incomeLog->getErrors()));
        }
    }
}