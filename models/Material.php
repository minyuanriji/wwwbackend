<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%material}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $wechat_app_id 公众号appid
 * @property string $media_id media_id
 * @property string|null $url url
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property string|null $media_type 类型：image video voice text
 * @property string|null $material_desc 视频描述
 */
class Material extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%material}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'wechat_app_id', 'media_id', 'name'], 'required'],
            [['mall_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
            [['wechat_app_id'], 'string', 'max' => 64],
            [['media_id'], 'string', 'max' => 128],
            [['url','material_desc'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 45],
            [['media_type'], 'string', 'max' => 12],
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
            'wechat_app_id' => '公众号appid',
            'media_id' => 'media_id',
            'url' => 'url',
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'media_type' => '类型：image video voice text',
            'material_desc'=>'描述'
        ];
    }
}