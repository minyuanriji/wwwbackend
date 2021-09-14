<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

class FromStoreBatchSaveForm extends BaseModel
{
    public $store;
    public $give_value;
    public $start_at;

    public function rules()
    {
        return [
            [['store', 'give_value'], 'required'],
            [['start_at'], 'string']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $add_id = [];
        try {
            if (is_array($this->store)) {
                foreach ($this->store as $value) {
                    $fromStore = ShoppingVoucherFromStore::findOne(['mch_id' => $value['mch_id'], 'store_id' => $value['store_id']]);
                    if ($fromStore) {
                        if (!$fromStore->is_delete) {
                            $add_id[] = $fromStore->mch_id;
                        } else {
                            $fromStore->is_delete = 0;
                        }
                    } else {
                        $fromStore = new ShoppingVoucherFromStore([
                            "mall_id" => \Yii::$app->mall->id,
                            "created_at" => time()
                        ]);
                        $fromStore->mch_id = $value['mch_id'];
                        $fromStore->store_id = $value['store_id'];
                        $fromStore->give_type = 1;
                        $fromStore->give_value = max(min($this->give_value, 100), 0);
                        $fromStore->name = $value['name'];
                        $fromStore->cover_url = $value['cover_url'];
                        $fromStore->start_at = strtotime($this->start_at);
                    }
                    $fromStore->updated_at = time();
                    if (!$fromStore->save()) {
                        throw new \Exception($this->responseErrorMsg($fromStore));
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功!(' . implode(',', $add_id) . ')已添加',
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }

    }
}