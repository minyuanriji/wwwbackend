<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;

class AlibabaDistributionGoodsDetailForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $user_id;
    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['mall_id', 'user_id', 'id'], 'integer']
        ];
    }


    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $goods = AlibabaDistributionGoodsList::findOne($this->id);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品不存在");
            }
            $aliInfo = (array)@json_decode($goods->ali_product_info, true);

            $detail['id']           = $goods->id;
            $detail['price']        = $goods->price;
            $detail['origin_price'] = $goods->origin_price;
            $detail['images']       = isset($aliInfo['info']['image']['images']) ? $aliInfo['info']['image']['images'] : [];
            $detail['saleInfo']     = $aliInfo['info']['saleInfo'];
            $detail['categoryName'] = $aliInfo['info']['categoryName'];
            $detail['mainVedio']    = $aliInfo['info']['mainVedio'];
            $detail['attributes']   = $aliInfo['info']['attributes'];
            $detail['shippingInfo'] = $aliInfo['info']['shippingInfo'];
            $detail['description']  = $aliInfo['info']['description'];

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'detail' => $detail
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        return [$this->id, $this->user_id, $this->mall_id];
    }
}