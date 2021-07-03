<?php
namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;
use app\models\Store;
use app\models\User;
use Yii;

/**
 * @property int $id
 * @property int $mall_id                   商家ID
 * @property int $mch_id                    商户ID
 * @property string $order_no               订单号
 * @property number $order_price            订单价格
 * @property number $pay_price              实际支付价格
 * @property int $is_pay                    是否已支付
 * @property int $pay_user_id               支付者用户ID
 * @property int $pay_at                    支付日期
 * @property int $score_deduction_price     积分抵扣数量
 * @property int $integral_deduction_price  购物卷抵扣数量
 * @property int $created_at                创建日期
 * @property int $updated_at                更新时间
 * @property int $is_delete                 是否已删除
 */
class MchCheckoutOrder extends BaseActiveRecord
{

    /** @var string 结账单支付 */
    const EVENT_PAYED = 'checkoutOrderPayed';
    /**
     * 1
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_checkout_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'order_no', 'order_price', 'created_at', 'updated_at'], 'required'],
            [['is_pay', 'mch_id', 'mall_id', 'pay_user_id', 'pay_at', 'created_at', 'updated_at', 'is_delete', 'store_id', 'commission_status', 'store_commission_status'], 'integer'],
            [['order_price', 'pay_price', 'score_deduction_price', 'integral_deduction_price', 'integral_fee_rate'], 'number'],
            [['order_no'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => '商城ID',
            'mch_id' => '商户ID',
            'order_no' => '订单号',
            'order_price' => '订单金额',
            'pay_price' => '实际支付金额',
            'is_pay' => '是否已支付',
            'pay_user_id' => '支付者用户ID',
            'pay_at' => '支付时间',
            'score_deduction_price' => '积分抵扣金额',
            'integral_deduction_price' => '红包券抵扣价',
            'created_at' => 'Create At',
            'updated_at' => 'Update At',
            'is_delete' => 'Delete At'
        ];
    }
    public function getMch(){
        return $this->hasOne(Mch::className(), ['id' => 'mch_id']);
    }

    public function getMchStore(){
        return $this->hasOne(Store::className(), ['mch_id' => 'mch_id']);
    }

    public function getPayUser(){
        return $this->hasOne(User::className(), ['id' => 'pay_user_id']);
    }
}
