<?php

namespace app\forms\mall\goods;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Label;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;
use yii\base\BaseObject;

class ShoppingSettingSaveForm extends BaseModel
{
    public $give_type;
    public $give_value;
    public $start_at;
    public $enable_express;
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['give_type', 'enable_express', 'goods_id'], 'integer'],
            [['start_at', 'give_value'], 'safe']
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

            $model = ShoppingVoucherFromGoods::findOne([
                "mall_id"  => $goods->mall_id,
                "goods_id" => $goods->id
            ]);
            if(!$model){
                $model = new ShoppingVoucherFromGoods([
                    "mall_id"    => $goods->mall_id,
                    "goods_id"   => $goods->id
                ]);
            }
            $model->give_type      = 1;
            $model->give_value     = max(0, min(100, $this->give_value));
            $model->updated_at     = time();
            $model->start_at       = max(time(), strtotime($this->start_at));
            $model->enable_express = $this->enable_express ? 1 : 0;

            if(!$model->save()){
                throw new \Exception($this->responseErrorMsg($model));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'保存成功', []);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }


    }
}