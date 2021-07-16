<?php
namespace app\plugins\addcredit\models;


use app\models\BaseActiveRecord;

class AddcreditPlateforms extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_addcredit_plateforms}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'name', 'sdk_dir', 'created_at', 'updated_at'], 'required'],
            [[], 'safe']
        ];
    }
}




