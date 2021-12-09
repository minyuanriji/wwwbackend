<?php

namespace app\forms\mall\goods;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Label;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetGoods;
use yii\base\BaseObject;

class ShoppingSettingExchangeSaveForm extends BaseModel
{
    public $exchange_rate;
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id', 'exchange_rate'], 'required'],
            [['goods_id'], 'integer'],
            [['exchange_rate'], 'safe']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $goods = Goods::findOne($this->goods_id);
            if (!$goods || $goods->is_delete)
                throw new \Exception('商品不存在');

            $model = ShoppingVoucherTargetGoods::findOne([
                "mall_id"  => $goods->mall_id,
                "goods_id" => $goods->id,
            ]);

            if(!$model){
                $model = new ShoppingVoucherTargetGoods([
                    "mall_id"    => $goods->mall_id,
                    "goods_id"   => $goods->id
                ]);
            }
            $model->name            = $goods->goodsWarehouse->name;
            $model->cover_pic       = $goods->goodsWarehouse->cover_pic;
            $model->voucher_price   = $this->exchange_rate;
            $model->is_delete       = 0;

            if(!$model->save())
                throw new \Exception($this->responseErrorMsg($model));

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'保存成功', []);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }


    }
}