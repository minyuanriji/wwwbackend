<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromAddcredit;

class FromAddcreditEditForm extends BaseModel
{
    public $id;
    public $sdk_key;
    public $fast_one_give;
    public $fast_follow_give;
    public $slow_one_give;
    public $slow_follow_give;

    public function rules()
    {
        return [
            [['sdk_key'], 'required'],
            [['fast_one_give', 'fast_follow_give', 'slow_one_give','slow_follow_give', 'id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->id) {
                $FromAddcredit = ShoppingVoucherFromAddcredit::findOne(['id' => $this->id]);
                if (!$FromAddcredit) {
                    throw new \Exception('数据异常,该条数据不存在');
                }
            } else {
                $FromAddcredit = new ShoppingVoucherFromAddcredit();
                $FromAddcredit->mall_id = \Yii::$app->mall->id;
                $FromAddcredit->sdk_key = $this->sdk_key;
            }
            $FromAddcredit->param_data_json = json_encode([
                'fast_one_give' => $this->fast_one_give,
                'fast_follow_give' => $this->fast_follow_give,
                'slow_one_give' => $this->slow_one_give,
                'slow_follow_give' => $this->slow_follow_give,
            ]);
            if (!$FromAddcredit->save()) {
                throw new \Exception('保存失败');
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}