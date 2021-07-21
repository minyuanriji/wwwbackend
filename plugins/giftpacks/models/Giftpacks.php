<?php
namespace app\plugins\giftpacks\models;

use app\models\BaseActiveRecord;

class Giftpacks extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_giftpacks}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'cover_pic', 'title', 'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'safe']
        ];
    }
}




