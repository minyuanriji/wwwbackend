<?php

namespace app\mch\forms\api;

use app\core\ApiCode;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\controllers\api\mana\MchAdminController;

class MchManaGiftPacksOrderForm extends BaseModel
{

    public $store_id;

    public function rules()
    {
        return [
            [['store_id'], 'integer']
        ];
    }

    public function getOrderList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {
            $list = [];
            $giftPacks = GiftpacksItem::find()->where(['store_id' => $this->store_id, 'is_delete' => 0])->asArray()->all();
            if ($giftPacks) {
                $giftpacksItemIds = array_column($giftPacks, 'id');
                $giftPacksOrderItem = GiftpacksOrderItem::find()->andWhere([
                    'and',
                    ['in', 'pack_item_id' , $giftpacksItemIds],
                ])->with('giftpacksItem')->asArray()->all();

                if ($giftPacksOrderItem) {
                    $orderIds = array_column($giftPacksOrderItem, 'order_id');
                    $giftpacksOrder = GiftpacksOrder::find()->andWhere([
                        'and',
                        ['in', 'id' , $orderIds],
                        ['is_delete' => 0],
                    ])->with('giftpacks')->asArray()->all();

                    if ($giftPacksOrderItem) {
                        foreach ($giftpacksOrder as $key => $packOrder) {
                            foreach ($giftPacksOrderItem as $item) {
                                if ($packOrder['id'] == $item['order_id']) {
                                    $giftpacksOrder[$key]['order_item'][] = $item;
                                }
                            }
                        }
                    }
                }
                $list = $giftpacksOrder;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", [
                'list' => $list,
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}