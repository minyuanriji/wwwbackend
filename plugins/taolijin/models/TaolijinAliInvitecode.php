<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinAliInvitecode extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_taolijin_ali_invitecode}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'ali_id', 'open_uid', 'code', 'updated_at', 'created_at'], 'required'],
            [['is_delete'], 'safe']
        ];
    }
}