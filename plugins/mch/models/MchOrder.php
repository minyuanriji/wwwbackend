<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%mch_order}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $is_transfer 是否转入商户0.否|1.是
 * @property string $updated_at
 */
class MchOrder extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'updated_at'], 'required'],
            [['order_id', 'is_transfer'], 'integer'],
            [['updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'is_transfer' => '是否转入商户0.否|1.是',
            'updated_at' => 'Updated At',
        ];
    }
}
