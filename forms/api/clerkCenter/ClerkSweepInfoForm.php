<?php

namespace app\forms\api\clerkCenter;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
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
        $result['cover_pic'] = '';
        $result['name'] = '';
        $result['type'] = 0;
        $result['infos'] = [];
        $result['id'] = 0;
        $result['goods_price'] = 0;
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
                    $giftPacksResult = GiftpacksItem::find()->alias('gpi')
                        ->innerJoin(["g" => Goods::tableName()], "g.id=gpi.goods_id")
                        ->select(['gpi.*', 'g.price as goods_price'])
                        ->andWhere(['gpi.id' => $giftOrderItemResult->pack_item_id, 'gpi.is_delete' => 0])->asArray()->one();
                    if (!$giftPacksResult) {
                        throw new \Exception('产品不存在');
                    }

                    $result['cover_pic'] = $giftPacksResult['cover_pic'];
                    $result['name'] = $giftPacksResult['name'];
                    $result['goods_price'] = $giftPacksResult['goods_price'];

                    $infos = [];
                    if($giftOrderItemResult->max_num > 0){
                        $infos[] = "还剩".$giftOrderItemResult->current_num."次";
                    }else{
                        $infos[] = "不限次数";
                    }
                    if($giftOrderItemResult->expired_at > 0){
                        $infos[] = date("Y-m-d", $giftOrderItemResult->expired_at) . "到期";
                    }else{
                        $infos[] = "永久有效";
                    }
                    $result['infos'] = implode("，", $infos);
                    $result['id'] = $clerkData->id;
                    $result['status'] = 'normal';
                }
            } elseif (
                $clerkData->source_type == 'normal_order' ||
                $clerkData->source_type == 'baopin_order' ||
                $clerkData->source_type == 'mch_normal_order' ||
                $clerkData->source_type == 'mch_baopin_order'
            ) {
                $orderResult = Order::find()->andWhere(['id' => $clerkData->source_id])->one();
                if (!$orderResult) {
                    $result['status'] = 'invalid';
//                    throw new \Exception('订单不存在');
                } else {
                    $orderDetailResult = OrderDetail::find()->where(['order_id' => $orderResult->id])->with('goods.goodsWarehouse')->one();

                    if (!$orderDetailResult) {
                        $result['status'] = 'invalid';
                    } else {
                        $result['cover_pic'] = $orderDetailResult->goodsWarehouse->cover_pic;
                        $result['name'] = $orderDetailResult->goodsWarehouse->name;
                        $result['goods_price'] = $orderDetailResult->total_price;
                    }

                    $infos = [];
                    if($orderResult->is_confirm == 1){
                        $infos[] = "还剩0次";
                    }else{
                        $infos[] = "还剩1次";
                    }
                    $infos[] = "永久有效";
                    $result['infos'] = implode("，", $infos);
                    $result['id'] = $clerkData->id;
                    $result['status'] = 'normal';
                }
            }
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