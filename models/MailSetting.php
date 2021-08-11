<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%mail_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $send_mail 发件人邮箱
 * @property string $send_pwd 授权码
 * @property string $send_name 发件人名称
 * @property string $receive_mail 收件人邮箱 多个用英文逗号隔开
 * @property int $status 是否开启邮件通知 0--关闭 1--开启
 * @property int $is_delete
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 */
class MailSetting extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mail_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'status', 'is_delete', 'mch_id'], 'integer'],
            [['send_mail', 'receive_mail'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'required'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['send_pwd', 'send_name'], 'string', 'max' => 255],
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
            'send_mail' => 'Send Mail',
            'send_pwd' => 'Send Pwd',
            'send_name' => 'Send Name',
            'receive_mail' => 'Receive Mail',
            'status' => 'Status',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
