<?php
namespace app\models;


class IntegralLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%integral_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'user_id', 'type', 'integral', 'current_integral',
              'desc', 'created_at', 'source_type'], 'required'],
            [['source_id'], 'safe']
        ];
    }
}