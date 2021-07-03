<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/19
 * Time: 10:19
 */


namespace app\plugins\sign_in\models;

use app\models\BaseActiveRecord;

class MemberLevel extends BaseActiveRecord
{

    public static function tableName()
    {
        return '{{%member_level}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level'], 'required'],
        ];
    }
}