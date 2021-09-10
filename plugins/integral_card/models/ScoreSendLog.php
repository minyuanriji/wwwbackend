<?php

namespace app\plugins\integral_card\models;

use app\models\BaseActiveRecord;

class ScoreSendLog extends BaseActiveRecord{


    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_score_send_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'user_id', 'source_id', 'source_type', 'status', 'created_at', 'updated_at'], 'required'],
            [['data_json', 'remark'], 'safe']
        ];
    }
}