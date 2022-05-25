<?php

namespace app\plugins\perform_distribution\models;


use app\models\BaseActiveRecord;

class Level extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_perform_distribution_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level', 'name',  'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'integer']
        ];
    }

}
