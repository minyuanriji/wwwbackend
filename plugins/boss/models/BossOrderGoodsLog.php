<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;
use app\models\CommonOrderDetail;
use app\models\User;
use Yii;


/**
 * This is the model class for table "{{%plugin_boss_order_goods_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $common_order_detail_id
 * @property int $user_id
 * @property float $price
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $type
 * @property User $user
 * @property  CommonOrderDetail $commonOrderDetail

 */
class BossOrderGoodsLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_order_goods_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'common_order_detail_id', 'user_id'], 'required'],
            [['mall_id', 'common_order_detail_id', 'user_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'type'], 'integer'],
            [['price'], 'number'],
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
            'common_order_detail_id' => 'Common Order Detail ID',
            'user_id' => 'User ID',
            'price' => 'Boss Price',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'type' => 'Type',
        ];
    }

    public function getUser(){
        return $this->hasOne(User::class,['id'=>'user_id']);
    }

    public function getCommonOrderDetail(){


        return $this->hasOne(CommonOrderDetail::class,['id'=>'common_order_detail_id']);

    }


}
