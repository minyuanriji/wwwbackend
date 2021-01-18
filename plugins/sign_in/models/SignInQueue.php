<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到队列model类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_sign_in_queue}}".
 *
 * @property int $id
 * @property string $token
 * @property string $data
 */
class SignInQueue extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_sign_in_queue}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token', 'data'], 'required'],
            [['data'], 'string'],
            [['token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'token' => 'Token',
            'data' => 'Data',
        ];
    }
}
