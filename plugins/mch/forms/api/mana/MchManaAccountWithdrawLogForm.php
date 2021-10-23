<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\MchCash;

class MchManaAccountWithdrawLogForm extends BaseModel{

    public function getList (){
        try {

            $mch_cash_list = MchCash::find()
                ->select(["id", "mall_id", "mch_id", "money", "content", "fact_price", "status", "transfer_status", "DATE_FORMAT(FROM_UNIXTIME(created_at),'%Y-%m-%d %H:%i:%s') as created_at"])
                ->where([
                    'mch_id'    => MchAdminController::$adminUser['mch_id'],
                    'mall_id'   => MchAdminController::$adminUser['mall_id'],
                    'is_delete' => 0,
                ])
                ->page($pagination, 10)
                ->orderBy('created_at desc')
                ->asArray()->all();

            if ($mch_cash_list) {
                foreach ($mch_cash_list as &$value) {
                    $value['service_charge'] = round($value['money'] - $value['fact_price'],2);
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $mch_cash_list,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }


}