<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\smart_shop\components\SmartShop;

class ShopDetailForm extends BaseModel implements ICacheForm {

    public $ss_store_id;
    public $plat;

    public function rules() {
        return [
            [['ss_store_id', 'plat'], 'required']
        ];
    }

    public function getCacheKey(){
        return [(int)$this->ss_store_id];
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $shop = new SmartShop();
            $detail = $shop->getStoreDetail($this->ss_store_id);
            if(!$detail){
                throw new \Exception("门店不存在");
            }

            $sourceData = $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                    'detail' => $detail
                ]
            );

            return new APICacheDataForm([
                "sourceData" => $sourceData
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}