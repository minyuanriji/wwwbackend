<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%user_coupon}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id 用户
 * @property int $coupon_id 优惠券
 * @property string $sub_price 满减
 * @property string $discount 折扣
 * @property string $discount_limit 折扣优惠上限
 * @property string $coupon_min_price 最低消费金额
 * @property int $type 优惠券类型：1=折扣，2=满减
 * @property int $begin_at 有效期开始时间
 * @property int $end_at 有效期结束时间
 * @property int $is_use 是否已使用：0=未使用，1=已使用
 * @property int $is_delete 删除
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property string $receive_type 获取方式
 * @property string $coupon_data 优惠券信息json格式
 * @property Coupon $coupon
 * @property int $is_failure 已失效
 */
class UserCoupon extends BaseActiveRecord
{
    public static $RECEIVE_TYPES = [
        0 => '商城发放', 1 => '购买商品奖励', 2 => '商品详情处领取', 3 => '领券中心领取',4 => '签到'
    ];
    /** @var int 折扣 */
    const TYPE_DISCOUNT = 1;
    /** @var int 满减 */
    const TYPE_REDUCTION = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_coupon}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'coupon_id', 'coupon_min_price', 'created_at', 'updated_at', 'deleted_at', 'coupon_data'], 'required'],
            [['mall_id', 'user_id', 'coupon_id', 'type', 'is_use', 'is_delete','is_failure'], 'integer'],
            [['sub_price', 'discount', 'coupon_min_price', 'discount_limit'], 'number'],
            [['begin_at', 'end_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['coupon_data'], 'string'],
            [['receive_type'], 'string', 'max' => 255],
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
            'user_id' => '用户',
            'coupon_id' => '优惠券',
            'sub_price' => '满减',
            'discount' => '折扣',
            'discount_limit' => '折扣优惠上限',
            'coupon_min_price' => '最低消费金额',
            'type' => '优惠券类型：1=折扣，2=满减',
            'begin_at' => '有效期开始时间',
            'end_at' => '有效期结束时间',
            'is_use' => '是否已使用：0=未使用，1=已使用',
            'is_delete' => '删除',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'receive_type' => '获取方式',
            'coupon_data' => '优惠券信息json格式',
            'is_failure'=>'已失效'
        ];
    }

    public function getAuto()
    {
        return $this->hasOne(UserCouponAuto::className(), ['user_coupon_id' => 'id']);
    }

    public function getCoupon()
    {
        return $this->hasOne(Coupon::className(), ['id' => 'coupon_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * 获取列表数据
     * @Author: zal
     * @Date: 2020-05-06
     * @Time: 10:33
     * @param array $wheres
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($wheres = []){
        $query = self::find();
        if(isset($wheres["mall_id"]) && !empty($wheres["mall_id"])){
            $query->andWhere(['mall_id' => $wheres["mall_id"]]);
        }

        if(isset($wheres["user_id"]) && !empty($wheres["user_id"])){
            $query->andWhere(['user_id' => $wheres["user_id"]]);
        }

        if(isset($wheres["is_use"])){
            $query->andWhere(['is_use' => $wheres["is_use"]]);
        }

        if(isset($wheres["is_failure"])){
            $query->andWhere(['is_failure' => $wheres["is_failure"]]);
        }

        if(isset($wheres["coupon_min_price"]) && !empty($wheres["coupon_min_price"])) {
            $query->andWhere(['<=', 'coupon_min_price', $wheres["coupon_min_price"]]);
        }
        $query->andWhere(['is_delete' => self::IS_DELETE_NO]);

        if((isset($wheres["begin_at"]) && !empty($wheres["begin_at"])) && (isset($wheres["end_at"]) && !empty($wheres["end_at"]))){
            $query->andWhere(['and',['<=', 'begin_at', $wheres["begin_at"]],['>=', 'end_at', $wheres["end_at"]]]);
        }else if(isset($wheres["begin_at"]) && !empty($wheres["begin_at"])){
            $query->andWhere(['and',['<=', 'begin_at', $wheres["begin_at"]]]);
        }else if(isset($wheres["end_at"]) && !empty($wheres["end_at"])){
            $query->andWhere(['and',['>=', 'end_at', $wheres["end_at"]]]);
        }

        $list = $query->select(['id','user_id','coupon_id','sub_price','sub_price','sub_price',
                                'discount','coupon_min_price','type','is_use','discount_limit','coupon_data'])
                    ->with(['coupon' => function ($query) {}])->orderBy(['id' => SORT_DESC])->all();
        return $list;
    }
}
