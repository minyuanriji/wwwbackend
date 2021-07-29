<?php
namespace app\models\clerk;

use app\models\BaseActiveRecord;

class ClerkData extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%clerk_data}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'process_class', 'app_platform', 'code', 'source_id', 'source_type', 'created_at', 'updated_at'], 'required'],
            [['status'], 'safe']
        ];
    }
}






