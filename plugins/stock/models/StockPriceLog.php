<?php


namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\CommonOrderDetail;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_stock_price_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property int $num
 * @property float $price
 * @property int $is_price
 * @property int $status
 * @property int $type 0货款
 * @property int $deleted_at
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $common_order_detail_id
 * @property float $income
 * @property User $user
 * @property CommonOrderDetail $commonOrderDetail
 */
class StockPriceLog extends BaseActiveRecord
{

    public static function tableName()
    {
        return '{{%plugin_stock_price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id'], 'required'],
            [['mall_id','common_order_detail_id', 'user_id', 'goods_id', 'num', 'is_price', 'status', 'type', 'deleted_at', 'created_at', 'updated_at', 'is_delete'], 'integer'],
            [['price','income'], 'number'],
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
            'goods_id' => 'Goods ID',
            'num' => 'Num',
            'price' => 'Price',
            'is_price' => 'Is Price',
            'status' => 'Status',
            'type' => '0货款',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'common_order_detail_id'=>'公共订单ID',
            'income'=>'收益'
        ];
    }
    public function getUser(){

        return $this->hasOne(User::class,['id'=>'user_id']);


    }

    public function getCommonOrderDetail(){

        return $this->hasOne(CommonOrderDetail::class,['id'=>'common_order_detail_id']);



    }
}
