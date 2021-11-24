<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;

class MchGroup extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mch_id', 'mall_id', 'store_id', 'created_at', 'updated_at'], 'required']
        ];
    }
}