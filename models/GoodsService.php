<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_service}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name 服务名称
 * @property string $remark 备注、描述
 * @property int $sort
 * @property int $is_default 默认服务
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 */
class GoodsService extends  BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'mch_id', 'sort', 'is_default', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
            [['name'], 'string', 'max' => 65],
            [['remark'], 'string', 'max' => 255],
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
            'name' => '服务名称',
            'remark' => '备注、描述',
            'sort' => 'Sort',
            'is_default' => '默认服务',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
