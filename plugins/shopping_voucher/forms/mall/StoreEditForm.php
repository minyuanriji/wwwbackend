<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\GoodsService;
use app\models\Store;
use app\models\User;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\Shopping_voucher\models\VoucherMch;

class StoreEditForm extends BaseModel
{
    public $id;
    public $mch_id;
    public $ratio;

    public function rules()
    {
        return [
            [['mch_id', 'ratio'], 'required'],
            [['ratio', 'mch_id', 'id'], 'integer'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->id) {
                $VoucherMch = VoucherMch::findOne($this->id);
                if (!$VoucherMch) throw new \Exception("数据异常,该条数据不存在");
            } else {
                $VoucherMch = VoucherMch::findOne(['mch_id' => $this->mch_id, 'is_delete' => 0]);
                if ($VoucherMch) throw new \Exception("请勿重复添加该商户！");
                $VoucherMch = new VoucherMch();
                $VoucherMch->mall_id = \Yii::$app->mall->id;
                $VoucherMch->mch_id = $this->mch_id;
                $VoucherMch->created_at = time();
            }
            $VoucherMch->updated_at = time();
            $VoucherMch->ratio = $this->ratio;
            if (!$VoucherMch->save()) throw new \Exception("保存失败");

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

    public function getDetail()
    {
        try {
            $detail = VoucherMch::find()->where([
                'id' => $this->id,
                'is_delete' => 0,
            ])->asArray()->one();
            if (!$detail) throw new \Exception('该记录不存在');
            $Store = Store::findOne(['mch_id' => $detail['mch_id']]);
            if (!$Store) {
                throw new \Exception('商户不存在');
            }
            $detail['name'] = $Store->name;

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'', $detail);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, $e->getMessage());
        }
    }
}