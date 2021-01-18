<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-签到奖励配置model类
 * Author: zal
 * Date: 2020-04-20
 * Time: 15:10
 */

namespace app\plugins\sign_in\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_sign_in}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $number 签到奖励数量
 * @property int $type 签到奖励类型1--积分|2--余额
 * @property int $day 签到天数
 * @property int $status 0--普通签到奖励 1--连续签到奖励 2--累计签到奖励
 * @property int $is_delete
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property string $token
 * @property int $award_id 签到奖励id
 */
class SignIn extends BaseActiveRecord
{
    /** @var int 签到奖励类型 积分 */
    const TYPE_SCORE = 1;
    /** @var int 签到奖励类型 余额 */
    const TYPE_BALANCE = 2;

    /** @var int 签到奖励状态 普通签到奖励 */
    const STATUS_NORMAL = 0;
    /** @var int 签到奖励状态 连续签到奖励 */
    const STATUS_CONTINUE = 1;
    /** @var int 签到奖励状态 累计签到奖励 */
    const STATUS_Total = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_sign_in}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'is_delete', 'created_at', 'updated_at', 'deleted_at', 'token'], 'required'],
            [['mall_id', 'user_id', 'day', 'status', 'is_delete', 'award_id'], 'integer'],
            [['number'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['type', 'token'], 'string', 'max' => 255],
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
            'number' => '签到奖励数量',
            'type' => '签到奖励类型1：integral--积分|2：balance--余额',
            'day' => '签到天数',
            'status' => '1--普通签到奖励 2--连续签到奖励 3--累计签到奖励',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'token' => 'Token',
            'award_id' => '签到奖励id',
        ];
    }
}
