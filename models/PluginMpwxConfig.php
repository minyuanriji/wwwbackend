<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%plugin_mpwx_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $app_id appid
 * @property string $secret 密钥
 * @property int $addtime
 * @property int $is_delete
 */
class PluginMpwxConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mpwx_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'app_id', 'secret', 'addtime', 'is_delete'], 'required'],
            [['mall_id', 'addtime', 'is_delete'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['app_id', 'secret'], 'string', 'max' => 64],
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
            'name' => 'Name',
            'app_id' => 'appid',
            'secret' => '密钥',
            'addtime' => 'Addtime',
            'is_delete' => 'Is Delete',
        ];
    }
}
