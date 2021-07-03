<?php


namespace app\plugins\agent\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_agent_price_log_type}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $price_log_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int $type 0、经销商提成 1、平级奖 2、越级奖
 */
class AgentPriceLogType extends BaseActiveRecord
{

    const TYPE_AGENT = 0;

    const TYPE_EQUAL = 1;

    const TYPE_OVER = 2;

    const PRICE_TYPE = [
        0 => '分红佣金',
        1 => '平级奖励',
        2 => '越级奖励'
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_agent_price_log_type}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'price_log_id'], 'required'],
            [['mall_id', 'price_log_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete', 'type'], 'integer'],
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
            'price_log_id' => 'Price Log ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'type' => '0、经销商提成 1、平级奖 2、越级奖',
        ];
    }


    public static function getTypeName($type)
    {
        if (!in_array($type, [0, 1, 2])) {
            return '未知类型';
        }
        return self::PRICE_TYPE[$type];
    }

}
