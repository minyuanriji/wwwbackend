<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%relation_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int|null $use_relation 是否启用关系链
 * @property int $get_power_way 1无条件、2、申请 3、或 4、与
 * @property int $buy_num_selected 消费次数达
 * @property int $buy_num 消费次数
 * @property int $buy_price_selected 消费金额达
 * @property float $buy_price 消费金额
 * @property int $buy_goods_selected 购买商品
 * @property int $buy_goods_way 1 任意商品  2 指定商品  3 指定分类
 * @property string|null $goods_ids 指定商品的goods_warehouse_id
 * @property string|null $cat_ids 指定分类
 * @property int $buy_compute_way 1、付款后 2完成后
 * @property int $become_child_way 1、首次点击分享链接 2、首次下单 3、首次付款
 * @property string|null $protocol 申请协议
 * @property string|null $notice 用户须知
 * @property string|null $status_pic_url 审核状态图片
 * @property int $is_delete
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property string $cat_list
 * @property string $goods_list
 * @property string|null $cash_type 提现类型 ["auto","wechat","balance","alipay","bank"]
 * @property float $cash_service_fee 提现手续费
 * @property float $min_money 每次最低提现金额
 * @property float $day_max_money 每天最多提现金额
 * @property int $is_income_cash
 *
 */
class RelationSetting extends BaseActiveRecord
{
    const GET_POWER_WAY_NO_CONDITION = 1; //无条件
    const GET_POWER_WAY_APPLY = 2;//申请
    const GET_POWER_WAY_OR = 3;//或
    const GET_POWER_WAY_AND = 4;//与
    const BUY_COMPUTE_WAY_PAY_AFTER = 1;//付款后计算
    const BUY_COMPUTE_WAY_FINISH_AFTER = 2;//订单完成后计算
    const BECOME_CHILD_WAY_LINK = 1;//点击链接
    const BECOME_CHILD_WAY_FIRST_ORDER = 2;//首次下单
    const BECOME_CHILD_WAY_FIRST_PAY = 3;//首次付款
    const BUY_GOODS_WAY_ANY_GOODS = 1;
    const BUY_GOODS_WAY_SELECTED_GOODS = 2;
    const BUY_GOODS_WAY_SELECTED_CAT = 3;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%relation_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id'], 'required'],
            [['mall_id', 'use_relation', 'get_power_way', 'buy_num_selected', 'buy_num', 'buy_price_selected', 'buy_goods_selected', 'buy_goods_way', 'buy_compute_way', 'become_child_way', 'is_delete', 'created_at', 'deleted_at', 'updated_at','is_income_cash'], 'integer'],
            [['buy_price', 'cash_service_fee', 'min_money', 'day_max_money'], 'number'],
            [['protocol', 'notice', 'goods_list'], 'string'],
            [['goods_ids', 'cat_ids', 'status_pic_url', 'cat_list', 'cash_type'], 'string', 'max' => 255],
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
            'use_relation' => '是否启用关系链',
            'get_power_way' => '1无条件、2、申请 3、或 4、与',
            'buy_num_selected' => '消费次数达',
            'buy_num' => '消费次数',
            'buy_price_selected' => '消费金额达',
            'buy_price' => '消费金额',
            'buy_goods_selected' => '购买商品',
            'buy_goods_way' => '1 任意商品  2 指定商品  3 指定分类',
            'goods_ids' => '指定商品的goods_warehouse_id',
            'cat_ids' => '指定分类',
            'buy_compute_way' => '1、付款后 2完成后',
            'become_child_way' => '1、首次点击分享链接 2、首次下单 3、首次付款',
            'protocol' => '申请协议',
            'notice' => '用户须知',
            'status_pic_url' => '审核状态图片',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'cat_list' => '分类列表',
            'goods_list' => '商品列表',
            'cash_type' => '提现类型 [\"auto\",\"wechat\",\"balance\",\"alipay\",\"bank\"]',
            'cash_service_fee' => '提现手续费',
            'min_money' => '每次最低提现金额',
            'day_max_money' => '每天最多提现金额',
            'is_income_cash'=>'开启收入提现'
        ];
    }
}
