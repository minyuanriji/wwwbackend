<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_coupon_auto}}".
 *
 * @property int $id
 * @property int $user_coupon_id
 * @property int $auto_coupon_id
 * @property int $is_delete 删除
 */
class UserCouponAuto extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_coupon_auto}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_coupon_id', 'auto_coupon_id'], 'required'],
            [['user_coupon_id', 'auto_coupon_id', 'is_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_coupon_id' => 'User Coupon ID',
            'auto_coupon_id' => 'Auto Coupon ID',
            'is_delete' => '删除',
        ];
    }
}
