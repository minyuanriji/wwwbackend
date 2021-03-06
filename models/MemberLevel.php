<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%member_level}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level 会员等级
 * @property string $name 等级名称
 * @property int $auto_update 是否自动升级
 * @property string $money 会员完成订单金额满足则升级
 * @property string $discount 会员折扣
 * @property int $status 状态 0--禁用 1--启用
 * @property string $pic_url 会员图片
 * @property string $bg_pic_url 会员背景图片
 * @property int $is_purchase 是否可购买
 * @property string $price 购买会员价格
 * @property string $rules 会员规则
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 */
class MemberLevel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
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
            [['mall_id', 'level', 'price', 'created_at', 'updated_at', 'deleted_at', 'is_purchase',
                'auto_update'], 'required'],
            [['mall_id', 'level', 'status', 'auto_update', 'is_purchase', 'is_delete','created_at', 'updated_at', 'deleted_at','buy_compute_way'], 'integer'],
            [['money', 'discount', 'price'], 'number'],
            [[ 'rules'], 'safe'],
            [['name'], 'string', 'max' => 65],
            [['pic_url', 'bg_pic_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'level' => '会员等级',
            'name' => '会员名称',
            'auto_update' => '是否自动升级状态',
            'money' => '会员升级金额',
            'discount' => '会员折扣',
            'status' => '会员状态',
            'pic_url' => '会员图标',
            'bg_pic_url' => '会员背景图',
            'is_purchase' => '是否可购买状态',
            'price' => '会员价格',
            'rules' => '会员规则',
            'buy_compute_way' => '升级方式，1=>付款 2=>完成',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getBenefits()
    {
        return $this->hasMany(MemberBenefit::className(), ['level_id' => 'id'])->where(['is_delete' => 0]);
    }
}
