<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%mch_mall_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $is_distribution 是否开启分销0.否|1.是
 * @property string $created_at
 */
class MchMallSetting extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_mall_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'created_at'], 'required'],
            [['mall_id', 'mch_id', 'is_distribution'], 'integer'],
            [['created_at'], 'safe'],
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
            'is_distribution' => '是否开启分销0.否|1.是',
            'created_at' => 'Created At',
        ];
    }
}
