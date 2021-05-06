<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\models\DistrictArr;
use app\plugins\mch\models\Mch;

class GetMchStoreController extends ApiController {

    /**
     * 获取商家店铺信息
     * @return \yii\web\Response
     */
    public function actionIndex(){

        $mchId = (int)$this->requestData['mch_id'];

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

            $this->asJson([
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'store'      => $store,
                    'category'   => $category
                ]
            ]);
        } catch (\Exception $e) {
            $this->asJson([
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}