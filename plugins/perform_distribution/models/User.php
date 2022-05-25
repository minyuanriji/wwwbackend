<?php

namespace app\plugins\perform_distribution\models;


use app\models\BaseActiveRecord;

class User extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_perform_distribution_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level_id', 'user_id',  'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'integer']
        ];
    }

}
