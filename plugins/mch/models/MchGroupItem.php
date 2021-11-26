<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;

class MchGroupItem extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_group_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'mch_id', 'mall_id', 'store_id', 'created_at', 'updated_at'], 'required']
        ];
    }
}