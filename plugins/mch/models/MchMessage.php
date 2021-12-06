<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;

class MchMessage extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_mch_message}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mch_id', 'mall_id', 'type', 'content', 'status',  'created_at', 'updated_at'], 'required'],
            [['fail_reason', 'try_count', 'admin_user_id'], 'safe']
        ];
    }
}