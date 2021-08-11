<?php

namespace app\plugins\commission\models;


use app\models\BaseActiveRecord;
use app\models\User;


class CommissionAddcredit3rPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_commission_addcredit_3r_price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'addcredit_order_id', 'user_id', 'price', 'status', 'created_at', 'updated_at'], 'required'],
            [['rule_data_json'], 'safe']
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}