<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\Shopping_voucher\models\VoucherGoods;
use app\plugins\Shopping_voucher\models\VoucherMch;

class DeleteForm extends BaseModel
{

    public $id;

    public function rules()
    {
        return [
            [['id'], 'required']
        ];
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $model = VoucherMch::findOne($this->id);
            if (!$model) {
                throw new \Exception("商户记录不存在");
            }

            $model->is_delete = 1;
            if (!$model->save()) {
                throw new \Exception("删除失败");
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}