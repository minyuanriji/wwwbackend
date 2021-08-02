<?php
/*
 * @link:http://www.@copyright: Copyright (c) @Author: Mr.Lin
 * @Email: 746027209@qq.com
 * @Date: 2021-07-06 14:13
 */

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\GiftpacksOrderItem;

class GiftpacksOrderItemDetailForm extends BaseModel{

    public $order_id;
    public $pack_item_id;
    public $city_id;
    public $longitude;
    public $latitude;

    public function rules(){
        return [
            [['order_id', 'pack_item_id', 'longitude', 'latitude'], 'required'],
            [['city_id'], 'integer']
        ];
    }

    public function getDetail(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $detail = GiftpacksItemDetailForm::detail($this->pack_item_id, $this->latitude, $this->longitude);

            $packOrderItem = GiftpacksOrderItem::findOne([
                "order_id"     => $this->order_id,
                "pack_item_id" => $this->pack_item_id
            ]);
            if(!$packOrderItem){
                throw new \Exception("无法获取服务项目");
            }

            $infos = [];
            if($packOrderItem->max_num > 0){
                $infos[] = "还剩".$packOrderItem->current_num."次";
            }else{
                $infos[] = "不限次数";
            }
            if($packOrderItem->expired_at > 0){
                $infos[] = date("Y-m-d", $packOrderItem->expired_at) . "到期";
            }else{
                $infos[] = "永久有效";
            }
            $detail['infos'] = implode("，", $infos);

            if(($packOrderItem->max_num > 0 && $packOrderItem->current_num <= 0) ||
                    ($packOrderItem->expired_at > 0 && $packOrderItem->expired_at < time())){
                $detail['is_available'] = 0;
            }else{
                $detail['is_available'] = 1;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $detail
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}