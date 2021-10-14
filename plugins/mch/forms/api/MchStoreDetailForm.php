<?php
namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\Store;
use app\plugins\mch\models\Mch;

class MchStoreDetailForm extends BaseModel implements ICacheForm {

    public $store_id;

    public function rules(){
        return [
            [['store_id'], 'required']
        ];
    }

    public function getCacheKey(){
        return [(int)$this->store_id];
    }

    public function getSourceDataForm(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $store = Store::findOne($this->store_id);
            if(!$store || $store->is_delete){
                throw new \Exception("门店不存在");
            }

            $mch = Mch::findOne($store->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status == Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户不存在");
            }

            $category = $mch->category;

            $detail = [];
            $detail['id']  = $store->id;
            $detail['mall_id'] = $store->mall_id;
            $detail['name'] = $store->name;
            $detail['category'] = !$category ? [] : ['id' => $category->id, 'name' => $category->name, 'pic_url' => $category->pic_url];


            $store = [];
            $store['mch_id']         = $mchStoreModel->mch_id;
            $store['mall_id']        = $mchStoreModel->mall_id;
            $store['latitude']       = $mchStoreModel->latitude;
            $store['longitude']      = $mchStoreModel->longitude;
            $store['address']        = $mchStoreModel->address;
            $store['logo']           = $mchStoreModel->cover_url;
            $store['service_mobile'] = $mchStoreModel->mobile;
            $store['pic_urls']       = (array)@json_decode($mchStoreModel->pic_url, true);
            if(isset($store['pic_urls'][0]) && empty($store['pic_urls'][0])){
                unset($store['pic_urls'][0]);
            }
            $store['name']           = $mchStoreModel->name;
            $store['description']    = $mchStoreModel->description;
            $store['scope']          = $mchStoreModel->scope;
            $store['district'] = [
                (int)$mchStoreModel->province_id,
                (int)$mchStoreModel->city_id,
                (int)$mchStoreModel->district_id
            ];

            try {
                $store['districts'] = DistrictArr::getDistrict((int)$mchStoreModel->province_id)['name'] .
                    DistrictArr::getDistrict((int)$mchStoreModel->city_id)['name'] .
                    DistrictArr::getDistrict((int)$mchStoreModel->district_id)['name'];
            } catch (\Exception $e) {
                $store['districts'] = '';
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'store'      => $store,
                        'category'   => $category
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}