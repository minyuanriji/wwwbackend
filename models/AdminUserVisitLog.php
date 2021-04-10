<?php

namespace app\models;

/**
 * This is the model class for table "{{%core_action_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id 操作人
 * @property string $link 链接
 * @property string $operate_ip 操作ip
 * @property int $created_at 创建时间
 */
class AdminUserVisitLog extends BaseActiveRecord
{

    public $isLog = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_visit_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'user_id', 'is_delete'], 'integer'],
            [['link','operate_ip'], 'string'],
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
            'user_id' => '用户id',
            'link' => '操作链接',
            'operate_ip' => '操作ip',
            'created_at' => 'Created At',
        ];
    }
}
