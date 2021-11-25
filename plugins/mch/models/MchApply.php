<?php
namespace app\plugins\mch\models;


use app\models\BaseActiveRecord;
use app\models\User;

class MchApply extends BaseActiveRecord
{
    const STATUS_REFUSED    = 'refused';    //拒绝
    const STATUS_PASSED     = 'passed';     //通过
    const STATUS_VERIFYING  = 'verifying';  //审核中
    const STATUS_APPLYING   = 'applying';   //申请中
    const DEFAULT_DISCOUNT  = 8;   //默认折扣

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_mch_apply}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'realname', 'mobile', 'user_id', 'status', 'created_at', 'updated_at', 'json_apply_data'], 'required'],
            [['remark', 'is_special_discount', 'mch_group_id'], 'safe']
        ];
    }
}
