<?php

namespace app\plugins\mch\models;


use app\models\BaseActiveRecord;

class MchSubAccount extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_sub_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'mch_id', 'created_at', 'updated_at'], 'required']
        ];
    }
}