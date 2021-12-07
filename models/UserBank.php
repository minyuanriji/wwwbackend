<?php

namespace app\models;

class UserBank extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_bank}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'realname', 'card_no', 'bank_name', 'account_type', 'created_at', 'updated_at'], 'required'],
            [['sub_bank'], 'safe']
        ];
    }
}