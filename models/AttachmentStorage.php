<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 图片上传渠道model
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:12
 */


namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%attachment_storage}}".
 *
 * @property string $id
 * @property int $mall_id
 * @property int $admin_id 存储设置所属账号
 * @property int $setting_id 存储配置id
 * @property int $type 存储类型：1=本地，2=阿里云，3=腾讯云，4=七牛
 * @property int $status 状态：0=未启用，1=已启用
 * @property string $created_at
 * @property string $updated_at
 */
class AttachmentStorage extends BaseActiveRecord
{
    /**
     * 本地
     */
    const STORAGE_TYPE_LOCAL = 1;
    /**
     * 阿里云
     */
    const STORAGE_TYPE_ALIOSS = 2;
    /**
     * 腾讯云
     */
    const STORAGE_TYPE_TXCOS = 3;
    /**
     * 七牛云
     */
    const STORAGE_TYPE_QINIU = 4;

    const STATUS_ON = 1;

    const STATUS_OFF = 0;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%attachment_storage}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'status', 'admin_id','setting_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            'type' => '存储类型：1=本地，2=阿里云，3=腾讯云，4=七牛',
            'setting_id' => '存储配置id',
            'status' => '状态：0=未启用，1=已启用',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'admin_id' => '存储设置所属账号',
        ];
    }

    public function getQiniuSetting()
    {
        return $this->hasOne(QiniuSetting::className(), ['id' => 'setting_id']);
    }

    public function getCosSetting()
    {
        return $this->hasOne(CosSetting::className(), ['id' => 'setting_id']);
    }

    public function getOssSetting()
    {
        return $this->hasOne(OssSetting::className(), ['id' => 'setting_id']);
    }
}