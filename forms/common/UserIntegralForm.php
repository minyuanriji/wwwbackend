<?php
namespace app\forms\common;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Cash;
use app\models\IncomeLog;
use app\models\IntegralLog;
use app\models\User;

class UserIntegralForm extends BaseModel{

    const TYPE_ADD      = 1;
    const TYPE_SUB      = 2;

    /**
     * 管理员充值
     * @param User $user
     * @param $price
     * @param string $remark
     * @return void
     */
    public static function adminAdd(User $user, $price, $admin_id, $remark = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$admin_id}]充值：" . $remark;
            static::change($user, $price, self::TYPE_ADD, "admin", null, $desc);

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
    public static function adminSub(User $user, $price, $admin_id, $remark = ""){
        $t = \Yii::$app->db->beginTransaction();
        try {

            $desc = "管理员[ID:{$admin_id}]扣减：" . $remark;

            static::change($user, $price, self::TYPE_SUB, "admin", null, $desc);

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

    private static function change(User $user, $price, $type, $source_type, $source_id, $desc = null){

        $staticIntegral = floatval($user->static_integral);

        if($type == 1){ //收入
            $user->static_integral = $staticIntegral + $price;
        }else{ //支出
            $user->static_integral = max(0, $staticIntegral - floatval($price));
        }

        if(!$user->save()){
            throw new \Exception(json_encode($user->getErrors()));
        }

        if($desc === null){
            $desc = "";
        }

        $integralLog = new IntegralLog([
            "mall_id"          => $user->mall_id,
            "user_id"          => $user->id,
            "type"             => $type,
            "current_integral" => $staticIntegral,
            "integral"         => floatval($price),
            "desc"             => $desc,
            "source_id"        => $source_id,
            "source_type"      => $source_type,
            "created_at"       => time()
        ]);
        if(!$integralLog->save()){
            throw new \Exception(json_encode($integralLog->getErrors()));
        }
    }
}