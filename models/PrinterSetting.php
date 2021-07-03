<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%printer_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $printer_id 打印机id
 * @property int $block_id 模板id
 * @property int $status 0关闭 1启用
 * @property int $is_attr 0不使用规格 1使用规格
 * @property string $type order(下单打印)-> 0关闭 1开启
 * pay (付款打印)-> 0关闭 1开启
 * confirm (确认收货打印)-> 0关闭 1开启
 * @property int $is_delete 删除
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $store_id
 * @property Printer $printer
 * @property Store $store
 */
class PrinterSetting extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%printer_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'printer_id', 'type'], 'required'],
            [['mall_id', 'printer_id', 'block_id', 'is_attr', 'is_delete', 'status', 'mch_id', 'store_id'], 'integer'],
            [['type'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'printer_id' => '打印机id',
            'block_id' => '模板id',
            'status' => '0关闭 1启用',
            'is_attr' => '0不使用规格 1使用规格',
            'type' => 'order(下单打印)-> 0关闭 1开启 pay (付款打印)-> 0关闭 1开启 confirm (确认收货打印)-> 0关闭 1开启',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'store_id' => 'Store ID',
        ];
    }

    public function getPrinter()
    {
        return $this->hasOne(Printer::className(), ['id' => 'printer_id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }
}
