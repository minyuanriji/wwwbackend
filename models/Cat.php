<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%cat}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $parent_id 父级ID
 * @property string $name 分类名称
 * @property string $pic_url
 * @property int $sort 排序，升序
 * @property string $big_pic_url
 * @property string $advert_pic 广告图片
 * @property string $advert_url 广告链接
 * @property int $status 是否启用:0.禁用|1.启用
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int|null $is_show
 * @property string $advert_open_type 打开方式
 * @property string $advert_params 导航参数
 * @property Cat $parent
 * @property Cat $child
 */
class Cat extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cat}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'mch_id', 'parent_id', 'sort', 'status', 'is_delete', 'is_show'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['advert_params'], 'string'],
            [['name'], 'string', 'max' => 45],
            [['pic_url', 'big_pic_url', 'advert_pic', 'advert_url'], 'string', 'max' => 255],
            [['advert_open_type'], 'string', 'max' => 65],
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
            'parent_id' => '父级ID',
            'name' => '分类名称',
            'pic_url' => 'Pic Url',
            'sort' => '排序，升序',
            'big_pic_url' => 'Big Pic Url',
            'advert_pic' => '广告图片',
            'advert_url' => '广告链接',
            'status' => '是否启用:0.禁用|1.启用',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'is_show' => 'Is Show',
            'advert_open_type' => '打开方式',
            'advert_params' => '导航参数',
        ];
    }

    public function getParent()
    {
        return $this->hasOne(Cat::className(), ['id' => 'parent_id']);
    }


    public function getChild()
    {
        return $this->hasMany(Cat::className(), ['parent_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

}
