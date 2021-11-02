<?php
namespace app\plugins\hotel\models;


use app\models\BaseActiveRecord;

class Seckill extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_seckill}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'name', 'start_time', 'end_time', 'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'safe']
        ];
    }
}