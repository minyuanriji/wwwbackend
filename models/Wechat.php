<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%wechat}}".
 *
 * @property int $id id
 * @property int $mall_id 商城ID
 * @property string $app_id appid
 * @property string $secret 密钥
 * @property string $token token
 * @property int $is_delete
 * @property string $aes_key 安全模式下aes_key
 * @property string|null $name 公众号名称
 * @property string|null $qrcode 公众号二维码
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 */
class Wechat extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wechat}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'app_id', 'secret', 'token', 'aes_key'], 'required'],
            [['mall_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'integer'],
            [['app_id', 'name'], 'string', 'max' => 45],
            [['secret', 'token', 'aes_key'], 'string', 'max' => 64],
            [['qrcode'], 'string', 'max' => 255],
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
            'app_id' => 'appid',
            'secret' => '密钥',
            'token' => 'token',
            'is_delete' => 'Is Delete',
            'aes_key' => '安全模式下aes_key',
            'name' => '公众号名称',
            'qrcode' => '公众号二维码',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }
}
