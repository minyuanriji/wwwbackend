<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinExchangeLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_exchange_log}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'tlj_goods_id', 'status', 'updated_at', 'created_at', 'integral_num', 'gift_price'], 'required'],
            [['result_data', 'rights_id'], 'safe']
        ];
    }
}