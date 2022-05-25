<?php

namespace app\plugins\perform_distribution\models;


use app\models\BaseActiveRecord;

class AwardOrder extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_perform_distribution_award_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'created_at', 'updated_at', 'order_id', 'order_detail_id'], 'required'],
            [['price'], 'number'],
            [['status', 'is_delete'], 'integer'],
            [['award_info'], 'safe']
        ];
    }

}
