<?php

namespace app\forms\api\clerkCenter;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;
use app\plugins\giftpacks\models\GiftpacksItem;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class ClerkSweepInfoForm extends BaseModel
{

    public $id;

    public function rules()
    {
        return [
            [['id'], 'required']
        ];
    }

    public function getInfo()
    {

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $result = [];
        try {
            $clerkData = ClerkData::findOne($this->id);
            if (!$clerkData) {
                throw new \Exception("核销数据不存在");
            }

            if ($clerkData->source_type == 'giftpacks_order_item') {
                $giftOrderItemResult = GiftpacksOrderItem::find()->andWhere(['id' => $clerkData->source_id])->one();
                if (!$giftOrderItemResult) {
                    $result['status'] = 'invalid';
//                    throw new \Exception('大礼包核销订单不存在');
                } else {
                    $giftPacksResult = GiftpacksItem::find()->andWhere(['id' => $giftOrderItemResult->pack_item_id, 'is_delete' => 0])->one();
                    if (!$giftPacksResult) {
                        throw new \Exception('产品不存在');
                    }

                    $result['cover_pic'] = $giftPacksResult->cover_pic;
                    $result['name'] = $giftPacksResult->name;

                    switch ($clerkData->source_type)
                    {
                        case 'giftpacks_order_item':
                            $result['type'] = 1;
                            break;
                        case 'normal_order':
                            $result['type'] = 2;
                            break;
                        case 'baopin_order':
                            $result['type'] = 3;
                            break;
                        case 'mch_normal_order':
                            $result['type'] = 4;
                            break;
                        case 'mch_baopin_order':
                            $result['type'] = 5;
                            break;
                        default:
                            $result['type'] = 0;
                    }

                    $result['current_num'] = $giftOrderItemResult->current_num;
                    $result['expired_at'] = $giftOrderItemResult->expired_at;
                    $result['id'] = $clerkData->id;
                    $result['status'] = 'normal';
                }
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }

    }

}