<?php
namespace app\plugins\commission\models;

use app\models\BaseActiveRecord;

class CommissionRuleChain extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_commission_rule_chain}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'rule_id', 'level', 'role_type', 'unique_key', 'created_at', 'updated_at', 'commisson_value'], 'required']
        ];
    }
}