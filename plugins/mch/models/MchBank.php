<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;

class MchBank extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_bank}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_id', 'realname', 'card_no', 'bank_name', 'account_type', 'created_at', 'updated_at'], 'required'],
            [['sub_bank'], 'safe']
        ];
    }
}