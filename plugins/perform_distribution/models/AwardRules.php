<?php

namespace app\plugins\perform_distribution\models;


use app\models\BaseActiveRecord;

class AwardRules extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_perform_distribution_award_rules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level_id', 'pd_goods_id', 'created_at', 'updated_at'], 'required'],
            [['award_money'], 'number'],
            [['award_type', 'is_delete'], 'integer']
        ];
    }

}
