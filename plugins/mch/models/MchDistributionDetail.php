<?php
namespace app\plugins\mch\models;


use app\models\BaseActiveRecord;

class MchDistributionDetail extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%plugin_mch_distribution_detail}}';
    }

    public function rules()
    {
        return [
            [['mch_id', 'commission_first', 'commission_second', 'commission_third',
              'level', 'created_at', 'updated_at'], 'required'],
        ];
    }
}