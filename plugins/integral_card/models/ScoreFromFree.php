<?php
namespace app\plugins\integral_card\models;

use app\models\BaseActiveRecord;

class ScoreFromFree extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_score_from_free}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'name', 'created_at', 'updated_at'], 'required'],
            [['number'], 'number'],
            [['deleted_at', 'is_delete', 'start_at', 'end_at', 'number', 'enable_score', 'enable_parent_award', 'score_setting'], 'safe']
        ];
    }

}
