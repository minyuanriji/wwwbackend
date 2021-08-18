<?php
namespace app\plugins\commission\models;


use app\models\BaseActiveRecord;
use app\models\User;

class CommissionGoodsPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_commission_goods_price_log}}';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'order_detail_id', 'goods_id', 'user_id', 'price', 'status', 'created_at', 'updated_at'], 'required'],
            [['is_lianc', 'rule_data_json'], 'safe']
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}