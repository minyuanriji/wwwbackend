<?php

namespace app\models\clerk;


use app\models\BaseActiveRecord;

class ClerkLog extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%clerk_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'clerk_data_id', 'user_id', 'created_at'], 'required'],
            [['remark'], 'safe']
        ];
    }
}




