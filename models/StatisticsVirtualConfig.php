<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/22
 * Time: 17:32
 */

namespace app\models;

/**
 * This is the model class for table "{{%statistics_virtual_config}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $set_type
 * @property double $total_transactions
 * @property double $today_earnings
 * @property int $user_sum
 * @property int $visitor_num
 * @property int $browse_num
 * @property string $member_level
 * @property string $province_data
 * @property int $conversion_browse_num
 * @property int $conversion_visitor_num
 * @property int $follow_num
 * @property int $order_visit_num
 * @property int $order_num
 * @property int $pay_num
 * @property int $is_delete
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_at
 * @property int $add_user
 */
class StatisticsVirtualConfig extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%statistics_virtual_config}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id','follow_num', 'set_type', 'user_sum','visitor_num','browse_num','conversion_browse_num','conversion_visitor_num','order_visit_num','order_num','pay_num','is_delete','created_at','updated_at','deleted_at','add_user'], 'integer'],
            [['total_transactions','today_earnings'], 'double'],
            [['province_data','member_level'], 'string'],
        ];
    }
}