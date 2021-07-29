<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class GiftpacksOrderClerkQrCodeForm extends BaseModel{

    public $pack_item_id;
    public $order_id;
    public $route_with_param;

    public function rules(){
        return [
            [['order_id', 'pack_item_id', 'route_with_param'], 'required']
        ];
    }

    public function getQrCode(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $order = GiftpacksOrder::findOne($this->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            if($order->user_id != \Yii::$app->user->id){
                throw new \Exception("无权限 " . \Yii::$app->user->id);
            }

            $orderPackItem = GiftpacksOrderItem::findOne([
                "order_id"     => $order->id,
                "pack_item_id" => $this->pack_item_id
            ]);
            if(!$orderPackItem){
                throw new \Exception("服务信息不存在");
            }

            if($orderPackItem->max_num > 0 && $orderPackItem->max_num <= 0){
                throw new \Exception("服务次数已用完");
            }

            if($orderPackItem->expired_at > 0 && $orderPackItem->expired_at < time()){
                throw new \Exception("服务已过期");
            }

            $appPlatform = \Yii::$app->appPlatform;
            if($appPlatform == User::PLATFORM_H5 || $appPlatform == User::PLATFORM_WECHAT){
                $dir = "giftpacks-order/offline-qrcode/" . $order->id . "/" . $orderPackItem->id . time() . '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                CommonLogic::createQrcode([], $this, $this->route_with_param, $dir);
                $res = ['file_path' => $imgUrl];
            }else{
                $qrCode = new QrCodeCommon();
                $res = $qrCode->getQrCode([], 100, $this->route_with_param);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $res
            ];
        }catch (\Exception $e){
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