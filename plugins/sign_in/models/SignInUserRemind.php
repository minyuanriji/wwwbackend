<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到用户提醒model类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:40
 */

namespace app\plugins\sign_in\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_sign_in_user_remind}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $date
 * @property int $is_remind
 * @property int $is_delete
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 */
class SignInUserRemind extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_sign_in_user_remind}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'date', 'is_remind', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'is_remind', 'is_delete'], 'integer'],
            [['date', 'created_at', 'deleted_at', 'updated_at'], 'safe'],
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
            'user_id' => 'User ID',
            'date' => 'Date',
            'is_remind' => 'Is Remind',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }
}
