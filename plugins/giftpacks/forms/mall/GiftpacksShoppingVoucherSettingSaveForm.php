<?php

namespace app\plugins\giftpacks\forms\mall;

use app\core\ApiCode;
use app\forms\mall\goods\BaseGoodsEdit;
use app\mch\forms\goods\GoodsEditForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\Store;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\mch\models\Mch;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;
use yii\base\BaseObject;

class GiftpacksShoppingVoucherSettingSaveForm extends BaseModel
{
    public $give_type;
    public $give_value;
    public $start_at;
    public $recommender;
    public $gift_id;

    public function rules()
    {
        return [
            [['gift_id', 'start_at', 'give_value'], 'required'],
            [['give_type', 'gift_id'], 'integer'],
            [['recommender'], 'safe'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $this->recommender = is_array($this->recommender) ? $this->recommender : [];

        try {
            $giftPacks = Giftpacks::findOne($this->gift_id);
            if (!$giftPacks || $giftPacks->is_delete)
                throw new \Exception('大礼包不存在');

            $model = ShoppingVoucherFromGiftpacks::findOne([
                "mall_id" => $giftPacks->mall_id,
                "pack_id" => $giftPacks->id
            ]);
            if(!$model){
                $model = new ShoppingVoucherFromGiftpacks([
                    "mall_id"    => $giftPacks->mall_id,
                    "pack_id"    => $giftPacks->id
                ]);
            }
            $model->give_type   = $this->give_type;
            $model->give_value  = max(0, min(100, $this->give_value));
            $model->start_at    = max(time(), strtotime($this->start_at));
            $model->recommender = @json_encode($this->recommender);
            if(!$model->save())
                throw new \Exception($this->responseErrorMsg($model));

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}