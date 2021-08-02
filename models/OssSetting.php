<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%oss_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $admin_id
 * @property string $access_key access_key
 * @property string $access_secret access_secret
 * @property string $bucket bucket
 * @property string $end_point 节点end_point
 * @property string $style_api
 * @property int $is_delete
 * @property int $created_at
 * @property Admin admin
 */
class OssSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%oss_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'access_key', 'access_secret', 'bucket', 'end_point', 'created_at'], 'required'],
            [['mall_id','admin_id', 'is_delete', 'created_at'], 'integer'],
            [['access_key', 'bucket', 'end_point',"style_api"], 'string', 'max' => 128],
            [['access_secret'], 'string', 'max' => 45],
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
            'admin_id' => 'admin ID',
            'access_key' => 'access_key',
            'access_secret' => 'access_secret',
            'bucket' => 'bucket',
            'end_point' => '节点end_point',
            'style_api' => 'style_api',
            'is_delete' => 'Is Delete',
            'created_at' => 'created_at',
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id','type' => 'type']);
    }
}
