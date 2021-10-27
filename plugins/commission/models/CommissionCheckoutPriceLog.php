<?php
namespace app\plugins\commission\models;


use app\models\BaseActiveRecord;
use app\models\User;
use yii\helpers\ArrayHelper;

class CommissionCheckoutPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_commission_checkout_price_log}}';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'checkout_order_id', 'user_id', 'price', 'status', 'created_at', 'updated_at'], 'required'],
            [['rule_data_json'], 'safe']
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getConsumptionCommission($where, $select)
    {
        $consumption = self::find()->where($where)->select($select)->all();
        $list = [];
        if ($consumption) {
            foreach ($consumption as $key => $item) {
                $nickname = $item->user->nickname;
                $item = ArrayHelper::toArray($item);
                $list[$key] = $item;
                $list[$key]['nickname'] = $nickname;
            }
        }
        return $list;
    }
}