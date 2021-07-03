<?php
/**
 * xuyaoxiang
 * 2020/08/24
 */
namespace app\plugins\group_buy\models;

use app\models\BaseActiveRecord;
use Yii;
use app\models\Order;
use app\models\User;

/**
 * This is the model class for table "{{%plugin_group_buy_active_user}}".
 *
 * @property int $id
 * @property int $active_id 拼单id;
 * @property int $order_id 拼团订单id;order.id;
 * @property int $is_creator 团长用户id
 * @property int $created_at
 * @property int $update_at
 * @property int $deleted_at
 * @property int $mall_id 商城id
 */
class PluginGroupBuyActiveItem extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_group_buy_active_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active_id', 'user_id','order_id', 'is_creator', 'mall_id','group_buy_price','attr_id'], 'required'],
            [['id', 'active_id', 'order_id', 'created_at', 'updated_at', 'deleted_at', 'mall_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id主键',
            'active_id' => '拼单id;',
            'order_id' => '拼团订单id;order.id;',
            'is_creator' => '团长用户id',
            'created_at' => 'Created At',
            'update_at' => 'Update At',
            'deleted_at' => 'Deleted At',
            'mall_id' => '商城id',
        ];
    }

    public function getActive()
    {
        return $this->hasOne(PluginGroupBuyActive::className(), ['id' => 'active_id']);
    }

    public function getOrder(){
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
