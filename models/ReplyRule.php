<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%reply_rule}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name 规则名称
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property string|null $reply_type 回复类型
 * @property string $content
 * @property RuleKeyword $kewords[]
 * @property integer $status
 */
class ReplyRule extends BaseActiveRecord
{

    const RULE_TYPE_ARTICLE = 'article';
    const RULE_TYPE_TEXT = 'text';
    const RULE_TYPE_IMAGE = 'image';
    const RULE_TYPE_VIDEO = 'video';
    const RULE_TYPE_VOICE = 'voice';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%reply_rule}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete','status'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['content'], 'string', 'max' => 255],
            [['reply_type'], 'string', 'max' => 10],
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
            'name' => '规则名称',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'reply_type' => '回复类型',
            'content' => '内容',
            'status'=>'启用状态'
        ];
    }

    public function getKeywords()
    {
        return $this->hasMany(RuleKeyword::className(), ['rule_id' => 'id']);
    }

}
