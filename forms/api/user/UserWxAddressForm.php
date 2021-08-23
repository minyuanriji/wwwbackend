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

class UserWxAddressForm extends BaseModel
{
    public $id;
    public $name;
    public $limit;
    public $mobile;
    public $detail;
    public $is_default;
    public $province;
    public $city;
    public $district;

    public function rules()
    {
        return [
            [['name', 'province', 'city', 'district', 'mobile', 'detail'], 'required'],
            [['name','province','city','district','detail'], 'string'],
            [['id', 'is_default', 'limit'], 'integer'],
            [['is_default',], 'default', 'value' => 0],
            [['name', 'mobile'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '收货人',
            'province' => '省份名称',
            'city' => '城市名称',
            'district' => '县区名称',
            'mobile' => '联系电话',
            'detail' => '详细地址',
        ];
    }

    /**
     * 保存微信收货地址
     * @return array
     */
    public function saveWx()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $DistrictArr = new DistrictArr();
        $province_id = $DistrictArr->getId($this->province);
        if (!$province_id) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "省份数据错误，请重新选择");
        }

        $city_id = $DistrictArr->getId($this->city,'city');
        if (!$city_id) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "城市数据错误，请重新选择");
        }

        $district_id = $DistrictArr->getId($this->district,'district');
        if (!$district_id) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, "地区数据错误，请重新选择");
        }

        $user_id = \Yii::$app->user->identity->id;
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
        $address->province_id = $province_id;
        $address->city_id = $city_id;
        $address->district_id = $district_id;
        $address->is_default = $this->is_default;
        if ($address->save()) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "保存成功",["id" => $address->id]);
        } else {
            return $this->returnApiResultData(999, "", $address);
        }
    }
}
