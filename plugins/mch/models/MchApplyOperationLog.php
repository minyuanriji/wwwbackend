<?php

namespace app\plugins\mch\models;

use app\models\Admin;
use app\models\BaseActiveRecord;
use app\models\User;

class MchApplyOperationLog extends BaseActiveRecord
{
    const OPERATION_PASSED  = 1;     //通过
    const OPERATION_REFUSED = 2;    //拒绝
    const OPERATION_SPECIAL = 3;  //特殊折扣申请

    const OPERATION_TERMINAL_RECEPTION = 1;  //前台
    const OPERATION_TERMINAL_BACKSTAGE = 2;  //后台

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_apply_operation_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'mch_apply_id', 'user_id', 'operation_terminal', 'operation', 'created_at'], 'required'],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'user_id']);
    }

    public function getMchApply()
    {
        return $this->hasOne(MchApply::className(), ['id' => 'mch_apply_id']);
    }
}
