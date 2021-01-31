<?php

namespace app\plugins\group_buy\models;
use app\models\BaseActiveRecord;
use app\models\Goods;
use app\models\GoodsAttr;

class PluginGroupBuyGoods extends BaseActiveRecord
{
    public static function tableName()
    {
        return '{{%plugin_group_buy_goods}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'people', 'vaild_time'], 'required'],
            [['mall_id', 'goods_id', 'people', 'vaild_time', 'status', 'virtual_people', 'is_virtual', 'send_score', 'send_balance','goods_stock','deleted_at'], 'integer'],
            [['start_at','deleted_at'], 'safe'],
            [['send_score', 'send_balance','goods_stock'], 'default', 'value' => 0]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'           => '拼团商品活动id',
            'mall_id'      => '商城id;',
            'goods_id'     => '拼团商品id',
            'people'       => '成团人数',
            'vaild_time'   => '有效时间',
            'status'       => '拼团商品状态;0未开始;1开团中;已结束2',
            'created_at'   => 'created_at',
            'update_at'    => 'update_at',
            'deleted_at'   => 'deleted_at',
            'send_score'   => '发放积分',
            'send_balance' => '发放余额',
            'goods_stock' => '拼团商品总库存',
        ];
    }

    public function getGoods(){
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getGoodsAttr(){
        return $this->hasMany(GoodsAttr::className(), ['goods_id' => 'goods_id']);
    }

    public function getGroupBuyGoodsAttr(){
        return $this->hasMany(PluginGroupBuyGoodsAttr::className(), ['attr_id' => 'id'])
            ->via('goodsAttr');
    }

    public function getActive(){
        return $this->hasMany(PluginGroupBuyActive::className(), ['group_buy_id' => 'id'])->where(['status' => 2]);
    }

    public function getActiveItem()
    {
        return $this->hasMany(PluginGroupBuyActiveItem::className(), ['active_id' => 'id'])
            ->via('active');
    }

    /**
     * @param $goods_id
     * @param $mall_id
     * @param array $status
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getGroupBuyGoodsOne($goods_id, $mall_id, $status = [])
    {
        $query = $this->getGroupBuyGoodsSql($goods_id, $mall_id, $status);

        return $query->one();
    }

    /**
     * @param $goods_id
     * @param $mall_id
     * @param array $status
     * @return \app\models\BaseActiveQuery
     */
    public function getGroupBuyGoodsSql($goods_id, $mall_id, $status = []){
        $query = PluginGroupBuyGoods::find()->where(['deleted_at' => 0, 'goods_id' => $goods_id, 'mall_id' => $mall_id]);
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        return $query;
    }

    /**
     * @param $goods_id
     * @param $mall_id
     * @param array $status
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getGroupBuyGoodsAll($goods_id, $mall_id, $status = [])
    {
        $query = $this->getGroupBuyGoodsSql($goods_id, $mall_id, $status);

        return $query->all();
    }
}