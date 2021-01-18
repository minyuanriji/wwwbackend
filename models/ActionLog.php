<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%core_action_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $operator 操作人
 * @property string $model 模型名称
 * @property int $model_id 模型ID
 * @property int $from 模型ID
 * @property string $before_update 更新之前的数据
 * @property string $after_update 更新之后的数据
 * @property int $created_at 创建时间
 * @property int $is_delete
 * @property string $remark
 * @property $user
 */
class ActionLog extends BaseActiveRecord
{
    /** @var int 来源1前台2后台 */
    const FROM_BEFORE = 1;
    const FROM_AFTER = 2;

    public $isLog = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%core_action_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'operator', 'model_id', 'before_update', 'after_update', 'created_at'], 'required'],
            [['mall_id', 'operator', 'model_id', 'is_delete','from'], 'integer'],
            [['before_update', 'after_update', 'model'], 'string'],
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
            'operator' => 'Admin ID',
            'model_id' => 'Model ID',
            'from' => '来源',
            'model' => 'Model',
            'before_update' => 'Before Update',
            'after_update' => 'After Update',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'remark' => 'Remark',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(Admin::className(), ['id' => 'operator']);
    }
}
