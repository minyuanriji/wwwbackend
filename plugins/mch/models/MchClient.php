<?php

namespace app\plugins\mch\models;

use app\models\BaseActiveRecord;

class MchClient extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_mch_client}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['token', 'fd', 'created_at', 'updated_at'], 'required'],
            [[], 'safe']
        ];
    }
}