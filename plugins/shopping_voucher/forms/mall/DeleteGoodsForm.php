<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\Shopping_voucher\models\VoucherGoods;

class DeleteGoodsForm extends BaseModel
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
            $model = VoucherGoods::findOne($this->id);
            if (!$model) {
                throw new \Exception("商品记录不存在");
            }

            if (!$model->delete()) {
                throw new \Exception("商品记录：" . $this->id . "删除失败了");
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}