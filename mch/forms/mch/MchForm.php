<?php
namespace app\mch\forms\mch;

use app\core\ApiCode;
use app\models\DistrictArr;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;

class MchForm extends BaseModel{

    public $mch_id;

    public function rules(){
        return [
            [["mch_id"], "integer"]
        ];
    }

    public function attributeLabels(){
        return [];
    }

    public function getDetail(){
        try {
            $detail = Mch::find()->where([
                'id'        => $this->mch_id ? $this->mch_id : \Yii::$app->mchAdmin->identity->mch_id,
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('user.userInfo', 'mchUser', 'store', 'category')->asArray()->one();

            if (!$detail) {
                throw new \Exception('商户不存在');
            }

            $detail['latitude_longitude'] = $detail['store']['longitude'] && $detail['store']['latitude'] ?
                $detail['store']['latitude'] . ',' . $detail['store']['longitude'] : '';
            $detail['address'] = $detail['store']['address'];
            $detail['logo'] = $detail['store']['cover_url'];
            $detail['service_mobile'] = $detail['store']['mobile'];
            $detail['bg_pic_url'] = \Yii::$app->serializer->decode($detail['store']['pic_url']);
            $detail['name'] = $detail['store']['name'];
            $detail['description'] = $detail['store']['description'];
            $detail['scope'] = $detail['store']['scope'];
            $detail['district'] = [
                (int)$detail['store']['province_id'],
                (int)$detail['store']['city_id'],
                (int)$detail['store']['district_id']
            ];
            try {
                $detail['districts'] = DistrictArr::getDistrict((int)$detail['store']['province_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['city_id'])['name'] .
                    DistrictArr::getDistrict((int)$detail['store']['district_id'])['name'];
            } catch (\Exception $e) {
                $detail['districts'] = '';
            }
            $detail['form_data'] = $detail['form_data'] ? \Yii::$app->serializer->decode($detail['form_data']) : [];

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
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
