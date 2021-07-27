<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%cart}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id 商品
 * @property string $attr_id 商品规格
 * @property int $num 商品数量
 * @property int $mch_id 商户id
 * @property int $is_delete 删除
 * @property int $sign 删除
 * @property string $attr_info 规格信息
 * @property string $created_at
 * @property string $deleted_at
 * @property string $updated_at
 * @property Store $store
 * @property Goods $goods
 * @property int $is_on_site_consumption 是否到店消费类商品
 * @property $attrs
 */
class Cart extends BaseActiveRecord
{
    /** @var string 购物车添加 */
    const EVENT_CART_ADD = 'cartAdd';

    /** @var string 购物车删除 */
    const EVENT_CART_DESTROY = 'cartDestroy';

    const CART_STATUS_CACHE = 'cart_status_cache';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%cart}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'attr_id', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'num', 'mch_id', 'is_delete', 'attr_id'], 'integer'],
            [['created_at', 'deleted_at', 'updated_at', 'sign', 'attr_info', 'mch_baopin_id'], 'safe'],
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
            'user_id' => 'User ID',
            'goods_id' => '商品',
            'attr_id' => '商品规格',
            'num' => '商品数量',
            'mch_id' => '商户id',
            'is_delete' => '删除',
            'sign' => '标记',
            'attr_info' => '规格信息',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 获取列表数据
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 16:33
     * @param array $wheres
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($wheres = []){
        $query = self::find()->alias('c');

        if(isset($wheres["mall_id"]) && !empty($wheres["mall_id"])){
            $query->andWhere(['c.mall_id' => $wheres["mall_id"]]);
        }

        if(isset($wheres["user_id"]) && !empty($wheres["user_id"])){
            $query->andWhere(['c.user_id' => $wheres["user_id"]]);
        }

        if(isset($wheres["goods_id"]) && !empty($wheres["goods_id"])){
            $query->andWhere(['c.goods_id' => $wheres["goods_id"]]);
        }

        if(isset($wheres["attr_id"]) && !empty($wheres["attr_id"])){
            $query->andWhere(['c.attr_id' => $wheres["attr_id"]]);
        }

        $query->andWhere(['c.is_delete' => self::IS_DELETE_NO]);

        if(isset($wheres["sign"]) && !empty($wheres["sign"])){
            $query->andWhere(['c.sign' => $wheres["sign"]]);
        }

        $list = $query->with(['goods.goodsWarehouse'])
                      ->with(['attrs.memberPrice' => function ($query) {
                            $query->where(['is_delete' => 0]);
                }])->select(['c.id', 'c.mch_id', 'c.goods_id','c.attr_id','c.num'])->orderBy(['c.id' => SORT_DESC])->all();

        return $list;
    }

    /**
     * 批量加入购物车
     * @param $array
     * @return int
     * @throws \yii\db\Exception
     */
    public static function batchAdd($array){
        return \Yii::$app->db->createCommand()
            ->batchInsert(
                self::tableName(),
                [
                    'mall_id',
                    'user_id',
                    'attr_id',
                    'goods_id',
                    'num',
                    'is_delete',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ],
                $array
            )->execute();
    }

    public static function cacheStatusGet()
    {
        $cart_status_cache = self::CART_STATUS_CACHE . \Yii::$app->user->id;
        return \Yii::$app->cache->get($cart_status_cache);
    }

    public static function cacheStatusSet(bool $info)
    {
        $cart_status_cache = self::CART_STATUS_CACHE . \Yii::$app->user->id;
        \Yii::$app->cache->set($cart_status_cache, $info, 0);
    }

    public function getAttrs()
    {
        return $this->hasOne(GoodsAttr::className(), ['id' => 'attr_id'])->where(['is_delete' => 0]);
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['mch_id' => 'mch_id']);
    }
}
