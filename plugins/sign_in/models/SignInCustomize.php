<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到控制model类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\models;

use app\models\BaseActiveRecord;

/**
 * This is the model class for table "{{%plugin_sign_in_customize}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $name
 * @property string $value
 * @property int $created_at
 * @property int $updated_at
 */
class SignInCustomize extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_sign_in_customize}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'value', 'created_at', 'updated_at'], 'required'],
            [['mall_id'], 'integer'],
            [['value'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
