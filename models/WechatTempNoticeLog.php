<?php

namespace app\models;

use Yii;

/**
 * 微信模板消息日志
 * This is the model class for table "{{%wechat_temp_notice_log}}".
 *
 * @property int $id
 * @property string|null $params 参数
 * @property string|null $result 返回值
 * @property int $created_at 创建时间
 */
class WechatTempNoticeLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat_temp_notice_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['params'], 'string'],
            [['created_at'], 'integer'],
            [['result'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'params' => '参数',
            'result' => '返回值',
            'created_at' => '创建时间',
        ];
    }
}
