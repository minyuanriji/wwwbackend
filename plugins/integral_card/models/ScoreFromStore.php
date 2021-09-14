<?php
namespace app\plugins\integral_card\models;

use app\models\BaseActiveRecord;

class ScoreFromStore extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_score_from_store}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'mch_id', 'store_id', 'created_at', 'updated_at'], 'required'],
            [['deleted_at', 'is_delete', 'start_at', 'rate', 'enable_score', 'score_setting'], 'safe']
        ];
    }

}
