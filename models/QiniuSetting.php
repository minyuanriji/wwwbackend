<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%qiniu_setting}}".
 *
 * @property int $id id
 * @property int $mall_id 商城ID
 * @property int $admin_id
 * @property string $access_key access_key
 * @property string $access_secret access_secret
 * @property string $domain 域名
 * @property int $created_at
 * @property int $is_delete
 * @property string $bucket bucket值
 * @property Admin admin
 */
class QiniuSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%qiniu_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'access_key', 'access_secret', 'domain', 'created_at', 'bucket'], 'required'],
            [['id', 'mall_id','admin_id', 'created_at', 'is_delete'], 'integer'],
            [['access_key', 'access_secret', 'domain'], 'string', 'max' => 128],
            [['bucket'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'mall_id' => '商城ID',
            'admin_id' => 'admin id',
            'access_key' => 'access_key',
            'access_secret' => 'access_secret',
            'domain' => '域名',
            'created_at' => 'created_at',
            'is_delete' => 'Is Delete',
            'bucket' => 'bucket值',
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id','type' => 'type']);
    }
}
