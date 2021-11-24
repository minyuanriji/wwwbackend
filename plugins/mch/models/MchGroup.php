<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;
use app\models\Store;

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
            [['mch_id', 'mall_id', 'store_id', 'created_at', 'updated_at'], 'required'],
            [['deleted_at', 'is_delete'], 'safe']
        ];
    }

    public function getMch(){
        return $this->hasOne(Mch::class, ["id" => "mch_id"]);
    }

    public function getStore(){
        return $this->hasOne(Store::class, ["id" => "store_id"]);
    }
}