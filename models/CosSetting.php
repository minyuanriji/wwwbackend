<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%cos_setting}}".
 *
 * @property int $id 腾讯云cos_id
 * @property int $mall_id 商城ID
 * @property int $admin_id 管理员id
 * @property string $region 区域region
 * @property string $secret_id secret_id
 * @property string $secret_key secret_key
 * @property int $is_delete
 * @property int $created_at
 * @property string $bucket bucket
 * @property Admin admin
 */
class CosSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cos_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'region', 'secret_id', 'secret_key', 'created_at', 'bucket'], 'required'],
            [['mall_id', 'is_delete', 'created_at'], 'integer'],
            [['region', 'bucket'], 'string', 'max' => 45],
            [['secret_id', 'secret_key'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '腾讯云cos_id',
            'mall_id' => '商城ID',
            'admin_id' => 'admin id',
            'region' => '区域region',
            'secret_id' => 'secret_id',
            'secret_key' => 'secret_key',
            'is_delete' => 'Is Delete',
            'created_at' => 'created_at',
            'bucket' => 'bucket',
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id','type' => 'type']);
    }
}
