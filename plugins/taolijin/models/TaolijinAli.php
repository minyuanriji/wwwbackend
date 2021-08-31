<?php


namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinAli extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_ali}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'ali_type', 'remark', 'settings_data', 'updated_at', 'created_at'], 'required'],
            [['sort', 'is_open', 'is_delete'], 'safe']
        ];
    }
}








