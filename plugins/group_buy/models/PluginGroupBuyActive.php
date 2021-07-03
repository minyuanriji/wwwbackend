<?php

namespace app\plugins\group_buy\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\models\Goods;

/**
 * This is the model class for table "jxmall_plugin_group_buy_active".
 *
 * @property int $id 开团id
 * @property int $goods_id 商品id;goods.id
 * @property int $people 成团人数;
 * @property int|null $virtual_people 虚拟成团人数;
 * @property int|null $actual_people 当前已拼人数;
 * @property int $creator_id 团长user_id;user_id.id;
 * @property string|null $start_at 开始时间
 * @property string|null $end_at 结束时间
 * @property int|null $status 拼单状态:0未拼单; 1拼单中; 2拼单成功; 3拼单失败;
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted_at
 * @property int|null $is_virtual 是否开启虚拟成团
 * @property int $mall_id 商城id
 */
class PluginGroupBuyActive extends BaseActiveRecord
{
    const EVENT_GROUP_BUY_ACTIVE_SUCCESS = "group_buy_active_success";

    const EVENT_GROUP_BUY_ACTIVE_FAILED = "group_buy_active_failed";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jxmall_plugin_group_buy_active';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'people', 'creator_id', 'mall_id', 'group_buy_id'], 'required'],
            [['goods_id', 'people', 'virtual_people', 'actual_people', 'creator_id', 'status', 'created_at', 'updated_at', 'deleted_at', 'is_virtual', 'mall_id', 'group_buy_id', 'is_manual', 'is_send'], 'integer'],
            [['start_at', 'end_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['status', 'deleted_at', 'virtual_people', 'actual_people', 'is_virtual', 'updated_at', 'cancel_people', 'is_manual','is_send'], 'default', 'value' => 0]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'             => '开团ID',
            'goods_id'       => 'Goods ID',
            'people'         => 'People',
            'virtual_people' => 'Virtual People',
            'actual_people'  => 'Actual People',
            'creator_id'     => 'Creator ID',
            'start_at'       => 'Start At',
            'end_at'         => 'End At',
            'status'         => 'Status',
            'created_at'     => 'Created At',
            'updated_at'     => 'Updated At',
            'deleted_at'     => 'Deleted At',
            'is_virtual'     => 'Is Virtual',
            'mall_id'        => 'Mall ID',
            'group_buy_id'   => '拼团商品活动id',
            'is_send'        => '是否发放奖励'
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id'])->select(['id', 'username', 'nickname', 'avatar_url']);
    }

    public function getGroupBuyGoods()
    {
        return $this->hasOne(PluginGroupBuyGoods::className(), ['id' => 'group_buy_id']);
    }

    public function getActiveItems()
    {
        return $this->hasMany(PluginGroupBuyActiveItem::className(), ['active_id' => 'id']);
    }
}
