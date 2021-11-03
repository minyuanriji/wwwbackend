<?php

namespace app\plugins\seckill\models;

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

    public function getSeckillGoods ()
    {
        return $this->hasMany(SeckillGoods::className(),['seckill_id' => 'id'])->where(['is_delete' => 0]);
    }
}