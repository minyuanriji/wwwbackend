<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%refund_address}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name
 * @property string $address
 * @property string $address_detail
 * @property string $mobile
 * @property string $remark
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 */
class RefundAddress extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%refund_address}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'mch_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
            [['name'], 'string', 'max' => 65],
            [['address', 'address_detail', 'mobile', 'remark'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'address' => 'Address',
            'address_detail' => 'Address Detail',
            'mobile' => 'Mobile',
            'remark' => 'Remark',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }
}
