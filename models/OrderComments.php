<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_comments}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property int $order_id
 * @property int $order_detail_id
 * @property int $user_id
 * @property int $score 评分：1=差评，2=中评，3=好
 * @property string $content 评价内容
 * @property string $pic_url 评价图片
 * @property int $is_show 是否显示：0.不显示|1.显示
 * @property int $is_virtual 是否虚拟用户
 * @property string $virtual_user 虚拟用户名
 * @property string $virtual_avatar 虚拟头像
 * @property string $reply_content 商家回复内容
 * @property integer $virtual_at 虚拟评价时间
 * @property string $goods_id 商品ID
 * @property string $goods_warehouse_id 商品库ID
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $sign;
 * @property int $is_anonymous
 * @property int $is_top
 * @property User $user
 * @property Goods $goods
 * @property OrderDetail $detail
 */
class OrderComments extends BaseActiveRecord
{
    /** @var int 是否匿名 是 */
    const IS_ANONYMOUS_YES = 1;
    /** @var int 是否匿名 否 */
    const IS_ANONYMOUS_NO = 0;

    /** @var int 是否置顶 是 */
    const IS_TOP_YES = 1;
    /** @var int 是否置顶 否 */
    const IS_TOP_NO = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order_comments}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'order_detail_id', 'user_id', 'score', 'pic_url', 'created_at', 'updated_at',
                'deleted_at', 'mch_id', 'goods_warehouse_id'], 'required'],
            [['mall_id', 'order_id', 'order_detail_id', 'user_id', 'score', 'is_show', 'is_virtual', 'is_delete',
                'goods_id', 'is_anonymous', 'mch_id', 'goods_warehouse_id', 'is_top', 'virtual_at'], 'integer'],
            [['content', 'pic_url', 'reply_content', 'sign'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['virtual_user', 'virtual_avatar', 'sign'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'order_id' => 'Order ID',
            'order_detail_id' => 'Order Detail ID',
            'user_id' => 'User ID',
            'score' => '评分：1=差评，2=中评，3=好',
            'content' => '评价内容',
            'pic_url' => '评价图片',
            'is_show' => '是否显示：0.不显示|1.显示',
            'is_virtual' => '是否虚拟用户',
            'virtual_user' => '虚拟用户名',
            'virtual_avatar' => '虚拟头像',
            'virtual_at' => '虚拟评价时间',
            'goods_id' => '商品',
            'goods_warehouse_id' => '商品库ID',
            'reply_content' => '商家回复内容',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'is_anonymous' => '是否匿名',
            'is_top' => '是否置顶',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getDetail()
    {
        return $this->hasOne(OrderDetail::className(),['id' => 'order_detail_id']);
    }
}
