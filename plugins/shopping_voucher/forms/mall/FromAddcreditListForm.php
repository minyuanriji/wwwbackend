<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromAddcredit;


class FromAddcreditListForm extends BaseModel
{
    public $sdk_key;

    public function rules()
    {
        return [
            [['sdk_key'], 'required'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $FromAddcredit = ShoppingVoucherFromAddcredit::findOne(['sdk_key' => $this->sdk_key]);
            if (!$FromAddcredit) {
                $result = [
                    'fast_one_give' => 0,
                    'fast_follow_give' => 0,
                    'slow_one_give' => 0,
                    'slow_follow_give' => 0,
                    'id' => 0,
                ];
            } else {
                $result = json_decode($FromAddcredit->param_data_json,true);
                $result['id'] = $FromAddcredit->id;
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', $result);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}