<?php

namespace app\plugins\addcredit\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\k_default\Code;
use app\plugins\addcredit\plateform\sdk\k_default\PlateForm;

class AccountForm extends BaseModel
{
    public function balanceQuery()
    {die;
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $plateforms = AddcreditPlateforms::find()->page($pagination)->asArray()->all();

            if ($plateforms) {
                $plate_form = new PlateForm();
                foreach ($plateforms as &$item) {
                    $item['json_param'] = @json_decode($item['json_param'],true);
                    $submit_res = $plate_form->accountBalanceQuery($item['json_param']);
                    if (!$submit_res) {
                        throw new \Exception('未知错误！', ApiCode::CODE_FAIL);
                    }
                    if ($submit_res->code != Code::BALANCE_QUERY_SUCCESS) {
                        throw new \Exception($submit_res->message, ApiCode::CODE_FAIL);
                    }
                    $item['balance'] = $submit_res->balance;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $plateforms,
                    'pagination' => $pagination
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}