<?php
namespace app\plugins\mch\models;


use app\models\BaseActiveRecord;
use app\models\User;

class MchApply extends BaseActiveRecord
{
    const STATUS_REFUSED = 'refused';
    const STATUS_PASSED = 'passed';

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
            [['remark', 'is_special_discount'], 'safe']
        ];
    }
}
