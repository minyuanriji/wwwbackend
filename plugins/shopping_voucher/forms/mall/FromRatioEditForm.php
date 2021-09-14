<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

class FromRatioEditForm extends BaseModel
{
    public $id;
    public $ratio;

    public function rules()
    {
        return [
            [['id', 'ratio'], 'required'],
            [['id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $fromStore = ShoppingVoucherFromStore::findOne($this->id);
            if (!$fromStore) {
                throw new \Exception("该门店不存在，异常！");
            }
            $fromStore->give_value = $this->ratio;
            $fromStore->updated_at = time();
            if (!$fromStore->save()) {
                throw new \Exception($this->responseErrorMsg($fromStore));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '编辑成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }
}