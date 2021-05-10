<?php
namespace app\mch\controllers\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\models\DistrictArr;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchVisitLog;

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

            //添加浏览记录
            if (\Yii::$app->user->id) {
                $this->addVisit($mchId, \Yii::$app->user->id);
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

    //添加浏览记录
    public function addVisit ($mch_id, $user_id)
    {
        try {
            $mch = Mch::findOne($mch_id);
            if (!$mch) {
                throw new \Exception('商户不存在');
            }

            if ($mch->user_id == $user_id) {
                return false;
            }

            /** @var MchVisitLog $model */
            $model = MchVisitLog::find()->where([
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $user_id,
                'mch_id' => $mch_id,
            ])->one();

            if ($model) {
                $model->num = $model->num + 1;
            } else {
                $model = new MchVisitLog();
                $model->mall_id = \Yii::$app->mall->id;
                $model->user_id = $user_id;
                $model->mch_id = $mch_id;
                $model->num = 1;
            }

            $res = $model->save();
            if (!$res) {
                throw new \Exception($model);
            }

            return true;

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}