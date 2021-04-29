<?php


namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use Yii;
/**
 * This is the model class for table "{{%plugin_stock_fill_income_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $fill_price_log_id
 * @property float $price
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $type 0 货款  1 平级奖
 * @property int $fill_order_detail_id
 */
class FillIncomeLog extends BaseActiveRecord
{
    //货款收益
    const LOAN_INCOME = 0;
    //平级奖励
    const EQUAL_INCOME = 1;
    //越级奖励
    const OVER_INCOME = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_fill_income_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'fill_price_log_id', 'created_at', 'deleted_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'fill_price_log_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'type','fill_order_detail_id'], 'integer'],
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
            'user_id' => 'User ID',
            'fill_price_log_id' => 'Fill Price Log ID',
            'price' => 'Price',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'type' => '0 货款  1 平级奖',
            'fill_order_detail_id'=>'订单详情ID'
        ];
    }
}
