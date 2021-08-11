<?php

namespace app\models;

use app\plugins\mch\models\Mch;
use Yii;

/**
 * This is the model class for table "{{%store}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 店铺名称
 * @property string $mobile 联系电话
 * @property string $address 地址
 * @property string $longitude 经度
 * @property string $latitude  纬度
 * @property int $score 店铺评分
 * @property int $province_id 省ID
 * @property int $city_id 市ID
 * @property int $district_id 区ID
 * @property string $cover_url 店铺封面图
 * @property string $pic_url 店铺轮播图
 * @property string $business_hours 营业时间
 * @property string $description 门店描述
 * @property int $is_default 默认平台0.否|1.是
 * @property int $scope 门店经营范围
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 */
class Store extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%store}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'mch_id', 'province_id', 'city_id', 'district_id'], 'integer'],
            [['name', 'description', 'mobile', 'address', 'longitude', 'latitude', 'score', 'is_default', 'is_delete', 'business_hours', 'cover_url', 'pic_url', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'mch_id' => 'Mch ID',
            'name' => '门店名称',
            'mobile' => '门店电话',
            'address' => '地址',
            'longitude' => '经度',
            'latitude' => '纬度',
            'score' => '店铺评分',
            'cover_url' => '店铺图片',
            'pic_url' => '店铺轮播图',
            'business_hours' => '营业时间',
            'description' => '店铺描述',
            'scope' => '门店经营范围',
            'is_default' => '是否默认门店',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted Time',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getMch ()
    {
        return $this->hasMany(Mch::className(), ['id' => 'each_id']);
    }
}
