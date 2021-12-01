<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinOrders extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_orders}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'ali_id', 'order_no', 'goods_id', 'user_id', 'order_status',  'pay_price', 'pay_at', 'pay_status',
             'updated_at', 'created_at', 'commission_price', 'commission_rate'], 'required'],
            [['origin_json_data', 'is_delete'], 'safe']
        ];
    }

}
