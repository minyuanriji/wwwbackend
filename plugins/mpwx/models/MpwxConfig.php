<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 微信配置
 * Author: zal
 * Date: 2020-04-14
 * Time: 15:50
 */


namespace app\plugins\mpwx\models;

use app\models\BaseActiveRecord;


/**
 * This is the model class for table "{{%plugin_mpwx_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $app_id appid
 * @property string $secret 密钥
 * @property int $is_delete
 * @property string|null $cert_pem
 * @property string|null $key_pem
 * @property int $mch_id
 * @property string|null $pay_secret 支付密钥
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property string|null $cert_pem_path cert_pem路径
 * @property string|null $key_pem_path key_pem路径
 */

class  MpwxConfig extends BaseActiveRecord
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
            [['mall_id', 'name', 'app_id', 'secret'], 'required'],
            [['mall_id', 'is_delete', 'mch_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['cert_pem', 'key_pem'], 'string'],
            [['name'], 'string', 'max' => 45],
            [['app_id', 'secret'], 'string', 'max' => 64],
            [['pay_secret', 'cert_pem_path', 'key_pem_path'], 'string', 'max' => 255],
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
            'is_delete' => 'Is Delete',
            'cert_pem' => 'Cert Pem',
            'key_pem' => 'Key Pem',
            'mch_id' => 'Mch ID',
            'pay_secret' => '支付密钥',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'cert_pem_path' => 'cert_pem路径',
            'key_pem_path' => 'key_pem路径',
        ];
    }
}
