<?php

namespace app\plugins\mch\models;


use app\models\BaseActiveRecord;

class MchPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'store_id', 'price', 'content', 'source_id', 'source_type', 'created_at', 'updated_at'], 'required'],
            [['status', 'other_json_data', 'remark'], 'safe'],
        ];
    }

}