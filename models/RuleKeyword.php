<?php


namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%rule_keyword}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $rule_id
 * @property string $keyword
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int $status
 * @property int $type 1包含   2 精准匹配
 */
class RuleKeyword extends BaseActiveRecord
{
    const TYPE_INCLUDE = 1;//包含关键词
    const TYPE_MATCH = 2;//精确匹配

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rule_keyword}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'rule_id', 'keyword'], 'required'],
            [['mall_id', 'rule_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete', 'type','status'], 'integer'],
            [['keyword'], 'string', 'max' => 45],
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
            'rule_id' => 'Rule ID',
            'keyword' => 'Keyword',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'type' => '1包含   2 精准匹配',
            'status'=>'0 未启用 1 启用'
        ];
    }
}