<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 接口-用户地址model
 * Author: zal
 * Date: 2020-05-05
 * Time: 10:16
 */

namespace app\forms\api\user;

use app\core\ApiCode;
use app\forms\common\DeliveryCommon;
use app\models\BaseModel;
use app\models\DistrictArr;
use app\models\Mall;
use app\models\UserAddress;
use app\validators\PhoneNumberValidator;
use function Sodium\add;

class UserAddressForm extends BaseModel
{
    public $id;
    public $name;
    public $limit;
    public $mobile;
    public $detail;
    public $is_default;
    public $province_id;
    public $city_id;
    public $district_id;
    public $latitude;
    public $longitude;
    public $location;
    public $hasCity;
    public $town_id;
    public $town;



    public function rules()
    {
        return [
            [['name', 'province_id', 'city_id', 'district_id', 'mobile', 'detail'], 'required'],
            [['detail', 'hasCity','town'], 'string'],
            [['id', 'province_id', 'city_id', 'district_id', 'is_default', 'limit','town_id'], 'integer'],
            [['is_default',], 'default', 'value' => 0],
            [['name', 'mobile', 'latitude', 'longitude', 'location'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
            [['latitude', 'longitude', 'location'], 'default', 'value' => ''],
            [['mobile'], PhoneNumberValidator::className(), 'when' => function ($model) {
                $mall = Mall::findOne(['id' => \Yii::$app->mall->id]);
                $status = $mall->getMallSettingOne('mobile_verify');
                return $status == 1;
            }],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '收货人',
            'province_id' => 'Province ID',
            'province' => '省份名称',
            'city_id' => 'City ID',
            'city' => '城市名称',
            'district_id' => 'District ID',
            'district' => '县区名称',
            'mobile' => '联系电话',
            'detail' => '详细地址',
            'latitude' => '定位地址',
            'longitude' => '定位地址',
            'location' => '定位地址',
            'town_id'=>'镇ID',
            'town'=>'镇名称'
        ];
    }

    /**
     * 自动读取地址列表
     * @return array
     */
    public function autoAddressInfo()
    {
        $districtArr = new DistrictArr();
        $districtArr = $districtArr::getArr();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '获取成功',
            //'data' => file_get_contents('statics' . DIRECTORY_SEPARATOR . 'text' . DIRECTORY_SEPARATOR . 'auto_address.json')
            'data' => $districtArr
        ];
    }

    /**
     * 获取用户地址列表
     * @return array
     */
    public function getList()
    {
        $user_id = \Yii::$app->user->identity->id;

        $list = UserAddress::find()->where([
            'is_delete' => 0,
            'user_id' => $user_id
        ])
            ->page($pagination, $this->limit)
            ->orderBy('is_default DESC,id DESC')
            ->asArray()
            ->all();

        $inPointList = [];
        $notInPointList = [];
        foreach ($list as $i => $item) {
            unset($list[$i]["created_at"], $list[$i]["updated_at"], $list[$i]["deleted_at"], $list[$i]["is_delete"]);
            $list[$i]['user_address'] = $item['province'] . $item['city'] . $item['district'] . $item['detail'];
            if ($this->hasCity == 'true') {
                if (!$item['longitude'] || !$item['latitude']) {
                    $notInPointList[] = $list[$i];
                } else {
                    try {
                        $config = DeliveryCommon::getInstance()->getConfig();
                        $range = $config['range'];
                        $point = [
                            'lng' => $item['longitude'],
                            'lat' => $item['latitude']
                        ];
                        if (is_point_in_polygon($point, $range)) {
                            $inPointList[] = $list[$i];
                        } else {
                            $notInPointList[] = $list[$i];
                        }
                    } catch (\Exception $exception) {
                        $notInPointList[] = $list[$i];
                    }
                }
            } else {
                $inPointList[] = $list[$i];
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $inPointList,
                'notInPointList' => $notInPointList,
            ]
        ];
    }

    /**
     * 用户地址详情
     * @return array
     */
    public function detail()
    {
        $user_id = \Yii::$app->user->identity->id;
        $userAddress = UserAddress::getOneData([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        if (!empty($userAddress)) {
            unset($userAddress["created_at"], $userAddress["updated_at"], $userAddress["deleted_at"], $userAddress["deleted_at"]);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => $userAddress,
        ];
    }

    /**
     * 设置用户默认地址
     * @return array
     */
    public function setDefaultAddress()
    {
        $user_id = \Yii::$app->user->identity->id;

        UserAddress::updateAll(['is_default' => 0], [
            'is_delete' => 0,
            'user_id' => $user_id
        ]);
        $model = UserAddress::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_default = $this->is_default;
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '操作成功'
        ];
    }

    /**
     * 删除
     * @return array
     */
    public function destroy()
    {
        $user_id = \Yii::$app->user->identity->id;
        $model = UserAddress::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id,
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    /**
     * 保存地址
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $province = DistrictArr::getDistrict($this->province_id);
        if (!$province) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "省份数据错误，请重新选择");
        }

        $city = DistrictArr::getDistrict($this->city_id);
        if (!$city) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "城市数据错误，请重新选择");
        }

        $district = DistrictArr::getDistrict($this->district_id);
        if (!$district) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "地区数据错误，请重新选择");
        }
        $user_id = \Yii::$app->user->identity->id;
        if ($this->is_default == 1) {
            UserAddress::updateAll(['is_default' => 0], [
                'is_delete' => 0,
                'user_id' => $user_id
            ]);
        }
        $address = UserAddress::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'user_id' => $user_id
        ]);
        if (!$address) {
            $address = new UserAddress();
            $address->is_delete = 0;
            $address->user_id = $user_id;
        }
        $address->attributes = $this->attributes;
        $address->province = $province->name;
        $address->city = $city->name;
        $address->district = $district->name;
        $address->is_default = $this->is_default;
        if($this->town_id){
            $address->town_id=$this->town_id;
        }
        if($this->town){
            $address->town=$this->town;
        }
        if ($address->save()) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "保存成功",["id" => $address->id]);
        } else {
            return $this->returnApiResultData(999, "", $address);
        }
    }
}
