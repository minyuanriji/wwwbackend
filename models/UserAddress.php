<?php

namespace app\models;

use yii\helpers\Html;
use Yii;

/**
 * This is the model class for table "{{%user_address}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name 收货人
 * @property int $province_id
 * @property string $province 省份名称
 * @property int $city_id
 * @property string $city 城市名称
 * @property int $district_id
 * @property string $district 县区名称
 * @property string $mobile 联系电话
 * @property string $detail 详细地址
 * @property int $is_default 是否默认
 * @property int $is_delete 删除
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property string $latitude 经度
 * @property string $longitude 纬度
 * @property string $location 位置
 * @property int $town_id 镇ID
 * @property string $town 镇
 */
class UserAddress extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_address}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'province_id', 'province', 'city_id', 'city', 'district_id', 'district', 'mobile', 'detail', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['user_id', 'province_id', 'city_id', 'district_id', 'is_default', 'is_delete','town_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'province', 'city', 'district', 'mobile', 'latitude', 'longitude', 'location','town'], 'string', 'max' => 255],
            [['detail'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => '收货人',
            'province_id' => 'Province ID',
            'province' => '省份名称',
            'city_id' => 'City ID',
            'city' => '城市名称',
            'district_id' => 'District ID',
            'district' => '县区名称',
            'mobile' => '联系电话',
            'detail' => '详细地址',
            'is_default' => '是否默认',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'latitude' => '经度',
            'longitude' => '纬度',
            'location' => '位置',
            'town_id'=>'镇ID',
            'town'=>'镇'
        ];
    }
    
    public function beforeSave($insert)
    {
        $this->name = Html::encode($this->name);
        $this->mobile = Html::encode($this->mobile);
        $this->detail = Html::encode($this->detail);
        return parent::beforeSave($insert);
    }

    /**
     * 获取用户默认地址
     * @param $where
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getUserAddressDefault($where){
        return self::find()->where($where)->orderBy("id desc")->one();
    }

}
