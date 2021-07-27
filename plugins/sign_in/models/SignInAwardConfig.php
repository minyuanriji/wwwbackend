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
 * This is the model class for table "{{%plugin_sign_in_award_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $number 奖励数量
 * @property int $day 领取奖励的天数
 * @property int $type 奖励类型integral--积分|balance--余额
 * @property int $status 领取类型1--普通签到领取|2--连续签到领取|3--累计签到领取
 * @property int $is_delete
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 */
class SignInAwardConfig extends BaseActiveRecord
{

    /** @var int 签到奖励类型 积分 */
    const TYPE_SCORE = 1;
    /** @var int 签到奖励类型 余额 */
    const TYPE_BALANCE = 2;
    /** @var int 签到奖励类型 余额 */
    const TYPE_COUPON = 3;
    const TYPE_COUPON_NAME = 'coupon';
    const TYPE_SCORE_NAME = 'integral';

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
        return '{{%plugin_sign_in_award_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'status', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'required'],
            [['mall_id','type', 'day', 'status', 'is_delete'], 'integer'],
            [['number'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'number' => '奖励数量',
            'day' => '领取奖励的天数',
            'type' => '奖励类型1:integral--积分|2:balance--余额',
            'status' => '领取类型1--普通签到领取|2--连续签到领取|3--累计签到领取',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getExplain()
    {
        $type = '';
        switch ($this->type) {
            case 1:
                $type = '积分';
                break;
            case 2:
                $type = '元';
                break;
            default:
        }
        return round($this->number, 2) . $type;
    }

    public function getAwardList($param,$fiend=''){
        $query = self::find()->where(['is_delete'=>0,'mall_id'=>$param['mall_id']]);
        if (isset($param['day'])){
            $query->andWhere(['day'=>$param['day']]);
        }
        if (isset($param['status'])){
            $query->andWhere(['status'=>$param['status']]);
        }
        if (!empty($fiend)){
            $query->select($fiend);
        }

        return $query->asArray()->all();
    }
}
