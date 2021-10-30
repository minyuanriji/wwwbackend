<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;
use app\plugins\mch\controllers\api\mana\MchAdminController;

class MchManaGiftPacksOrderForm extends BaseModel
{

    public function getOrderList()
    {
        try {
            $list = [];
            $storeId = MchAdminController::$adminUser['store']['id'];
            $giftPacks = GiftpacksItem::find()->where(['store_id' => $storeId, 'is_delete' => 0])->asArray()->all();
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