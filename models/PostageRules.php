<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%postage_rules}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name
 * @property string $detail 规则详情
 * @property int $status 是否默认
 * @property int $type 计费方式【1=>按重计费、2=>按件计费】
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 */
class PostageRules extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%postage_rules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'detail'], 'required'],
            [['mall_id', 'mch_id', 'status', 'type', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
            [['detail'], 'string'],
            [['name'], 'string', 'max' => 65],
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
            'detail' => '规则详情',
            'status' => '是否默认',
            'type' => '计费方式【1=>按重计费、2=>按件计费】',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function decodeDetail()
    {
        $detail = Yii::$app->serializer->decode($this->detail);
        foreach ($detail as &$item) {
            foreach ($item as &$value) {
                if (is_numeric($value)) {
                    $value = floatval($value);
                }
            }
            unset($value);
        }
        unset($item);
        return $detail;
    }
}
