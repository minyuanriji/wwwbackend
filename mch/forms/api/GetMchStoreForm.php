<?php


namespace app\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchVisitLog;

class GetMchStoreForm extends BaseModel {

    public $mch_id;

    public function rules(){
        return [
            [['mch_id'], 'required']
        ];
    }

    public function getDetail(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $mchId = (int)$this->mch_id;

        try {
            $mchModel = Mch::find()->where([
                'id'        => $mchId,
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->one();
            $mchStoreModel = $mchModel ? $mchModel->store : null;

            if (!$mchModel || !$mchStoreModel) {
                throw new \Exception('商家店铺不存在');
            }

            $categoryModel = $mchModel->category;
            $category = [
                'id'      => $categoryModel ? $categoryModel->id : 0,
                'name'    => $categoryModel ? $categoryModel->name : '',
                'pic_url' => 'http://'
            ];

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

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'store'      => $store,
                    'category'   => $category
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

}