<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use Yii;
use yii\base\Model;

/**
 * This is the model class for table "{{%plugin_distribution_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $key
 * @property string $value
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 * @property int $is_delete 是否删除 0--未删除 1--已删除
 * @property int $deleted_at 删除时间
 */
class DistributionSetting extends BaseActiveRecord
{
    const LEVEL = 'level'; // 分销层级0关闭1一级分销2二级分销3三级分销
    const IS_SELF_BUY = 'is_self_buy'; // 分销内购
    const PRICE_TYPE = 'price_type'; // 分销佣金类型1百分百；2固定金额
    const FIRST_PRICE = 'first_price'; // 一级佣金
    const SECOND_PRICE = 'second_price'; // 二级佣金
    const THIRD_PRICE = 'third_price'; // 三级佣金
    const PAY_TYPE = 'pay_type'; // 提现方式
    const CASH_MAX_DAY = 'cash_max_day'; // 每日提现上限
    const MIN_MONEY = 'min_money'; // 最少提现金额
    const CASH_SERVICE_FEE = 'cash_service_fee'; // 提现手续费
    const PAY_TYPE_LIST = ['auto' => '自动打款', 'wechat' => '微信线下转账', 'alipay' => '支付宝线下转账',
        'bank' => '银行线下转账', 'balance' => '提现到余额'];
    const IS_SHOW_SHARE_LEVEL = 'is_show_share_level'; // 是否显示分销商等级升级入口
    const IS_APPLY = 'is_apply'; // 是否需要申请
    const IS_CHECK = 'is_check'; // 是否需要审核
    const REBUY_PRICE_DATE = 'rebuy_price_date';//复购结算日期
    const IS_REBUY = 'IS_REBUY'; // 开启复购奖励
    const IS_TEAM = 'is_team';
    const IS_SUBSIDY = 'is_subsidy';
    const SUBSIDY_PRICE_DATE = 'subsidy_price_date';
    const PROTOCOL = 'protocol';//协议


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'key', 'value'], 'required'],
            [['mall_id', 'created_at', 'updated_at', 'is_delete', 'deleted_at'], 'integer'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 255],
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
            'key' => 'Key',
            'value' => 'Value',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'is_delete' => '是否删除 0--未删除 1--已删除',
            'deleted_at' => '删除时间',
        ];
    }

    /**
     * @param null $key
     * @return bool|string|void
     */
    public static function getValueByKey($key = null, $mall_id = null)
    {
        if (!$key) {
            return false;
        }
        if (!$mall_id) {
            $mall_id = Yii::$app->mall->id;
        }
        $model = DistributionSetting::findOne(['mall_id' => $mall_id, 'is_delete' => 0, 'key' => $key]);
        if ($model) {
            return $model->value;
        }
        return false;
    }

    public static function strToNumber($key, $str)
    {
        $default = ['level', 'is_self_buy', 'price_type', 'share_condition',
            'share_goods_status', 'first_price', 'second_price', 'third_price', 'cash_max_day', 'min_money', 'is_check', 'is_apply',
            'cash_service_fee_rate', 'is_show_share_level', 'rebuy_price_date', 'is_rebuy', 'is_team','is_subsidy','subsidy_price_date'];
        if (in_array($key, $default)) {
            return round($str, 2);
        }
        return $str;
    }

    /**
     * 获取分销配置，并组成key-value数组
     * @param $mallId
     * @return array
     */
    public static function getData($mallId)
    {
        $list = self::find()->where(['mall_id' => $mallId, 'is_delete' => 0])->all();
        $newList = [];
        /* @var self[] $list */
        foreach ($list as $item) {
            $newList[$item->key] = self::strToNumber($item->key, Yii::$app->serializer->decode($item->value));
        }
        if (!isset($newList[self::IS_SHOW_SHARE_LEVEL])) {
            $newList[self::IS_SHOW_SHARE_LEVEL] = 1;
        }
        return $newList;
    }
}
