<?php
namespace app\plugins\commission\models;


use app\models\BaseActiveRecord;

class CommissionGiftpacksPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_commission_giftpacks_price_log}}';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'pack_id', 'user_id', 'price', 'status', 'created_at', 'updated_at'], 'required'],
            [['rule_data_json'], 'safe']
        ];
    }

}