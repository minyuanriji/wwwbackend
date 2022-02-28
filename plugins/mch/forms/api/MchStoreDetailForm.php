<?php
namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\helpers\CityHelper;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;

class MchStoreDetailForm extends BaseModel implements ICacheForm {

    public $store_id;
    public $mch_id;
    public $longitude;
    public $latitude;

    public function rules(){
        return [
            [['store_id'], 'required'],
            [['mch_id'], 'integer'],
            [['longitude', 'latitude'], 'safe']
        ];
    }

    public function getCacheKey(){
        return [(int)$this->mch_id, (int)$this->store_id, $this->longitude, $this->latitude];
    }

    public function getSourceDataForm(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            if(!empty($this->mch_id)){
                $store = Store::findOne(["mch_id" => $this->mch_id]);
                if(!$store || $store->is_delete){
                    throw new \Exception("门店不存在");
                }
            }else{
                $store = Store::findOne($this->store_id);
                if(!$store || $store->is_delete){
                    throw new \Exception("门店不存在");
                }
            }



            $mch = Mch::findOne($store->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户不存在");
            }

            $category = $mch->category;

            $detail = [];
            $detail['id']             = $store->id;
            $detail['mch_id']          = $store->mch_id;
            $detail['mall_id']        = $store->mall_id;
            $detail['name']           = $store->name;
            $detail['category']       = !$category ? [] : ['id' => $category->id, 'name' => $category->name, 'pic_url' => $category->pic_url];
            $detail['latitude']       = $store->latitude;
            $detail['longitude']      = $store->longitude;
            $detail['address']        = $store->address;
            if(!preg_match("/^https?:\/\//i", trim($store->cover_url))){
                $store->cover_url =  $this->host_info . "/web/static/header-logo.png";
            }
            $detail['logo']           = $store->cover_url;
            $detail['phone']          = $store->mobile;
            $detail['score']          = $store->score;
            $detail['business_hours'] = $store->business_hours;
            $detail['pic_urls']       = @json_decode($store->pic_url, true);
            if ($detail['pic_urls']) {
                foreach ($detail['pic_urls'] as &$urls) {
                    if (isset($urls['pic_url'])) {
                        $urls = $urls['pic_url'];
                    }
                }
            }
            $detail['pic_urls']       = is_array($detail['pic_urls']) ? $detail['pic_urls'] : [];
            $detail['description']    = $store->description;
            $detail['scope']          = $store->scope;

            $cityData = CityHelper::reverseData($store->district_id, $store->city_id, $store->province_id);
            $detail['province']      = isset($cityData['province']['name']) ? $cityData['province']['name'] : "";
            $detail['city']          = isset($cityData['city']['name']) ? $cityData['city']['name'] : "";
            $detail['district']      = isset($cityData['district']['name']) ? $cityData['district']['name'] : "";
            $detail['region_name']   = $detail['district'] ? $detail['district'] : ($detail['city'] ? $detail['city'] : $detail['province']);
            $detail['remark']        = "付100送100红包";

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'detail'      => $detail
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