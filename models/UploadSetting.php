<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%upload_setting}}".
 *
 * @property int $id id
 * @property int $mall_id 商城ID
 * @property int $type 1、本地 2阿里云 3腾讯云 4 七牛云
 * @property int $setting_id 上传模板ID
 * @property int $created_at created_at
 * @property int $is_delete is_delete
 */
class UploadSetting extends BaseActiveRecord
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%upload_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'type', 'setting_id', 'created_at', 'is_delete'], 'required'],
            [['mall_id', 'type', 'setting_id', 'created_at', 'is_delete'], 'integer'],
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
            'type' => '1、本地 2阿里云 3腾讯云 4七牛云',
            'setting_id' => '上传模板ID',
            'created_at' => 'created_at',
            'is_delete' => 'is_delete',
        ];
    }

    public function getCosSetting()
    {
        return $this->hasOne(CosSetting::className(), ['setting_id' => 'id','type' => 'type']);
    }

    public function getOssSetting()
    {
        return $this->hasOne(OssSetting::className(), [['setting_id' => 'id','type' => 'type']]);
    }

    public function getQiniuSetting()
    {
        return $this->hasOne(QiniuSetting::className(), ['setting_id' => 'id','type' => self::STORAGE_TYPE_QINIU]);
    }
}
