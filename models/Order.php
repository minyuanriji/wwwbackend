<?php

namespace app\models;

use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchOrder;
use app\plugins\vip_card\models\VipCardDiscount;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property string $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $mch_id 多商户id，0表示商城订单
 * @property string $order_no 订单号
 * @property string $total_price 订单总金额(含运费)
 * @property string $total_pay_price 实际支付总费用(含运费）
 * @property string $express_original_price 运费(后台修改前)
 * @property string $express_price 运费(后台修改后)
 * @property string $total_goods_price 订单商品总金额(优惠后)
 * @property string $total_goods_original_price 订单商品总金额(优惠前)
 * @property string $member_discount_price 会员优惠价格(正数表示优惠，负数表示加价)
 * @property int $use_user_coupon_id 使用的用户优惠券id
 * @property string $coupon_discount_price 优惠券优惠金额
 * @property int $use_score 使用积分数量
 * @property string $score_deduction_price 积分抵扣金额
 * @property string $name 收件人姓名
 * @property string $mobile 收件人手机号
 * @property string $address 收件人地址
 * @property string $remark 用户订单备注
 * @property string $order_form 自定义表单（JSON）
 * @property string $distance 同城距离
 * @property string $city_mobile 同城配送联系方式
 * @property string $words 商家留言
 * @property string $seller_remark 商家订单备注
 * @property int $is_pay 是否支付：0.未支付|1.已支付
 * @property int $pay_type 支付方式：1.在线支付 2.货到付款 3.余额支付
 * @property string $pay_at 支付时间
 * @property int $is_send 是否发货：0.未发货|1.已发货
 * @property int $send_at 发货时间
 * @property string $express 物流公司
 * @property string $express_no 物流订单号
 * @property int $is_sale 是否过售后时间
 * @property int $is_confirm 收货状态：0.未收货|1.已收货
 * @property int $confirm_at 确认收货时间
 * @property int $cancel_status 订单取消状态：0.未取消|1.已取消|2.申请取消
 * @property int $cancel_at 订单取消时间
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $is_delete
 * @property int $is_recycle 是否加入回收站 0.否|1.是
 * @property int $send_type 配送方式：0--快递配送 1--到店自提 2--同城配送
 * @property string $offline_qrcode 核销码
 * @property int $clerk_id 核销员ID
 * @property int $store_id 自提门店ID
 * @property string $sign 订单标识，用于区分插件
 * @property string $token
 * @property string $is_comment 是否评价0.否|1.是
 * @property int $comment_at
 * @property string $support_pay_types 支持的支付方式，空表示支持系统设置支持的所有方式
 * @property int $sale_status 是否申请售后
 * @property int $status 订单状态|1.已完成|0.进行中不能对订单进行任何操作
 * @property int $back_price 后台优惠(正数表示优惠，负数表示加价)
 * @property int $auto_cancel_at 自动取消时间
 * @property int $auto_confirm_at 自动确认收货时间
 * @property int $auto_sales_at 自动售后时间
 * @property int $complete_at 完成时间
 * @property string $location 定位
 * @property string $city_name 配送员
 * @property string $city_info
 * @property OrderRefund[] $refund
 * @property OrderDetail $detail
 * @property UserCard[] $userCards
 * @property User $user
 * @property User $clerk
 * @property $comments
 * @property Store $store
 * @property Mch $mch
 * @property string $signName
 * @property string customer_name;
 * @property OrderDetailExpressRelation $detailExpressRelation;
 * @property $expressRelation;
 * @property OrderDetailExpress $detailExpress;
 * @property OrderExpressSingle $expressSingle;
 * @property PaymentOrder $paymentOrder;
 * @property integer $address_id
 * @property int province_id
 */
class Order extends BaseActiveRecord
{
    /** @var string 订单创建 */
    const EVENT_CREATED = 'orderCreated';

    /** @var string 订单取消 */
    const EVENT_CANCELED = 'orderCanceled';

    /** @var string 订单改价 */
    const EVENT_CHANGE_PRICE = 'orderChangePrice';

    /** @var string 订单支付 */
    const EVENT_PAYED = 'orderPayed';

    /** @var string 订单发货 */
    const EVENT_SENT = 'orderSent';

    /** @var string 订单确认收货 */
    const EVENT_CONFIRMED = 'orderConfirmed';

    /** @var string 订单过售后 */
    const EVENT_SALES = 'orderSales';

    /** 取消状态 */
    /** @var int 未取消 */
    const CANCEL_STATUS_NO = 0;
    /** @var int 已取消 */
    const CANCEL_STATUS_YES = 1;
    /** @var int 申请取消 */
    const CANCEL_STATUS_APPLY_CANCEL = 2;

    /** 支付方式 */
    /** @var int 未支付 */
    const PAY_TYPE_NO_PAY = 0;
    /** @var int 在线支付 */
    const PAY_TYPE_ONLINE = 1;
    /** @var int 货到付款 */
    const PAY_TYPE_GOODS_PAY = 2;
    /** @var int 余额支付 */
    const PAY_TYPE_BALANCE_PAY = 3;
    /** @var int 支付宝支付 */
    const PAY_TYPE_ALIPAY_PAY = 4;
    /** @var int 百度支付 */
    const PAY_TYPE_BAIDU_PAY = 5;
    /** @var int 头条支付 */
    const PAY_TYPE_TOUTIAO_PAY = 6;

    /** 配送方式 */
    /** @var int 快递配送 */
    const SEND_TYPE_EXPRESS = 0;
    /** @var int 到店自提 */
    const SEND_TYPE_SELF = 1;
    /** @var int 同城配送 */
    const SEND_TYPE_CITY = 2;

    /** 是否支付 */
    /** @var int 是 */
    const IS_PAY_YES = 1;
    /** @var int 否 */
    const IS_PAY_NO = 0;

    /** 是否评价 */
    /** @var int 是 */
    const IS_COMMENT_YES = 1;
    /** @var int 否 */
    const IS_COMMENT_NO = 0;

    /** 是否售后 */
    /** @var int 是 */
    const IS_SALE_YES = 1;
    /** @var int 否 */
    const IS_SALE_NO = 0;

    /** 是否确认收货 */
    /** @var int 是 */
    const IS_CONFIRM_YES = 1;
    /** @var int 否 */
    const IS_CONFIRM_NO = 0;

    /** @var int 是否申请售后0否1是 2已完成售后*/
    const SALE_STATUS_YES = 1;
    const SALE_STATUS_NO = 0;
    const SALE_STATUS_FINISHED = 2;

    /** @var int 订单状态 */
    const STATUS_WAIT_PAY = 0;
    const STATUS_WAIT_DELIVER = 1;
    const STATUS_WAIT_RECEIVE = 2;
    const STATUS_WAIT_COMMENT = 3;
    const STATUS_CANCEL_WAIT = 4;
    const STATUS_CLOSE = 5;
    const STATUS_SALES_APPLY = 6;
    const STATUS_SALES_COMPLETE = 7;
    const STATUS_COMPLETE = 8;

    /**
     * 订单状态数组
     * @var array
     */
    public $status_array = [
        self::STATUS_WAIT_PAY => "未支付",
        self::STATUS_WAIT_DELIVER => "待发货",
        self::STATUS_WAIT_RECEIVE => "待收货",
        self::STATUS_WAIT_COMMENT => "待评价",
        self::STATUS_CANCEL_WAIT => "取消待处理",
        self::STATUS_CLOSE => "已取消/已关闭",
        self::STATUS_SALES_APPLY => "售后申请中",
        self::STATUS_SALES_COMPLETE => "售后完成",
        self::STATUS_COMPLETE => "已完成",
    ];


    /**
     * 支付方式
     * @var array
     */
    public $pay_type_array = [
        self::PAY_TYPE_NO_PAY => "未支付",
        self::PAY_TYPE_ONLINE => "在线支付",
        self::PAY_TYPE_GOODS_PAY => "货到付款",
        self::PAY_TYPE_BALANCE_PAY => "余额支付",
        self::PAY_TYPE_ALIPAY_PAY => "支付宝支付",
        self::PAY_TYPE_BAIDU_PAY => "百度支付",
        self::PAY_TYPE_TOUTIAO_PAY => "头条支付",
    ];

    /**
     * 配送方式
     * @var array
     */
    public $send_type_array = [
        self::SEND_TYPE_EXPRESS => "快递配送",
        self::SEND_TYPE_SELF => "到店自提",
        self::SEND_TYPE_CITY => "同城配送",
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'total_price', 'total_pay_price', 'express_original_price', 'express_price',
                'total_goods_price', 'total_goods_original_price', 'member_discount_price',
                 'use_score', 'score_deduction_price', 'pay_type', 'created_at',
                'updated_at', 'deleted_at'], 'required'],
            [['mall_id', 'user_id', 'mch_id', 'use_user_coupon_id', 'use_score', 'is_pay', 'pay_type',
                'is_send', 'is_sale', 'is_confirm', 'cancel_status', 'is_delete', 'is_recycle', 'send_type',
                'clerk_id', 'store_id', 'is_comment', 'sale_status', 'status', 'distance','address_id','province_id'], 'integer'],
            [['total_price', 'total_pay_price', 'express_original_price', 'express_price', 'total_goods_price',
                'total_goods_original_price', 'member_discount_price', 'coupon_discount_price',
                'score_deduction_price', 'back_price','full_relief_price','integral_deduction_price'], 'number'],
            [['order_form', 'support_pay_types'], 'string'],
            [['pay_at', 'send_at', 'confirm_at', 'cancel_at', 'created_at', 'updated_at', 'deleted_at',
                'comment_at', 'auto_cancel_at', 'auto_confirm_at', 'auto_sales_at','complete_at'], 'safe'],
            [['order_no', 'mobile', 'address', 'remark', 'words', 'seller_remark', 'express_no', 'offline_qrcode',
                'sign', 'location', 'city_name', 'city_info'], 'string', 'max' => 255],
            [['name', 'customer_name', 'express'], 'string', 'max' => 65],
            [['token'], 'string', 'max' => 32],
            [['city_mobile'], 'string', 'max' => 100],
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
            'mch_id' => '多商户id，0表示商城订单',
            'order_no' => '订单号',
            'total_price' => '订单总金额(含运费)',
            'total_pay_price' => '实际支付总费用(含运费）',
            'express_original_price' => '运费(后台修改前)',
            'express_price' => '运费(后台修改后)',
            'total_goods_price' => '订单商品总金额(优惠后)',
            'total_goods_original_price' => '订单商品总金额(优惠前)',
            'member_discount_price' => '会员优惠价格(正数表示优惠，负数表示加价)',
            'use_user_coupon_id' => '使用的用户优惠券id',
            'coupon_discount_price' => '优惠券优惠金额',
            'use_score' => '使用积分数量',
            'score_deduction_price' => '积分抵扣金额',
            'name' => '收件人姓名',
            'mobile' => '收件人手机号',
            'address' => '收件人地址',
            'remark' => '用户订单备注',
            'order_form' => '自定义表单（JSON）',
            'words' => '商家留言',
            'seller_remark' => '商家订单备注',
            'is_pay' => '是否支付：0.未支付|1.已支付',
            'pay_type' => '支付方式：1.在线支付 2.货到付款 3.余额支付 4.支付宝支付 5.百度支付 6.头条支付',
            'pay_at' => '支付时间',
            'is_send' => '是否发货：0.未发货|1.已发货',
            'send_at' => '发货时间',
            'express' => '物流公司',
            'express_no' => '物流订单号',
            'is_sale' => '是否过售后时间',
            'is_confirm' => '收货状态：0.未收货|1.已收货',
            'confirm_at' => '确认收货时间',
            'cancel_status' => '订单取消状态：0.未取消|1.已取消|2.申请取消',
            'cancel_at' => '订单取消时间',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'complete_at' => 'complete At',
            'is_delete' => 'Is Delete',
            'is_recycle' => '是否加入回收站 0.否|1.是',
            'send_type' => '配送方式：0--快递配送 1--到店自提 2--同城配送',
            'offline_qrcode' => '核销码',
            'clerk_id' => '核销员ID',
            'store_id' => '自提门店ID',
            'sign' => '订单标识，用于区分插件',
            'token' => 'Token',
            'is_comment' => '是否评价',
            'comment_at' => '评价时间',
            'support_pay_types' => '支持的支付方式，空表示支持系统设置支持的所有方式',
            'sale_status' => '是否申请售后;0未售后;1申请售后;2售后完成',
            'status' => '订单状态0未支付1待发货2待收货3待评价4取消待处理5已取消/已关闭6售后申请中7售后完成8已完成',
            'back_price' => '后台优惠(正数表示优惠，负数表示加价)',
            'auto_cancel_at' => '自动取消时间',
            'auto_confirm_at' => '自动确认收货时间',
            'auto_sales_at' => '自动售后时间',
            'customer_name' => '京东快递商户编码',
            'distance' => '同城距离',
            'city_mobile' => '同城配送联系方式',
            'location' => '定位',
            'city_name' => '配送员',
            'city_info' => '配送信息',
            'address_id'=>'地址id',
            'full_relief_price' =>'满减金额',
            'integral_deduction_price' =>'红包券抵扣金额',
        ];
    }

    public function getDetail()
    {
        return $this->hasMany(OrderDetail::className(), ['order_id' => 'id']);
    }

    /**
     * 订单状态文本信息
     * @param null $order
     * @return mixed|string
     * @throws \Exception
     */
    public function orderStatusText($order = null)
    {
        if (!$order) {
            $order = $this;
        }
        if (!$order) {
            throw new \Exception('order不能为空');
        }
        if (is_array($order)) {
            $order = (object)$order;
        }

        try {
//            if ($order->is_pay == 0 && $order->pay_type != 2) {
//                return '待付款';
//            } elseif ($order->is_send == 0) {
//                return $order->send_type == 1 ? '待核销' : '待发货';
//            } elseif ($order->is_send == 1 && $order->is_confirm == 0) {
//                return $order->send_type == 1 ? '待核销' : '待收货';
//            } elseif ($order->is_confirm == 1 && $order->is_sale == 0) {
//                return $order->send_type == 1 ? '已核销' : '已收货';
//            } elseif ($order->is_sale == 1) {
//                return '已完成';
//            } else {
//                return '未知状态';
//            }
            return $this->status_array[$order->status];
        } catch (\Exception $exception) {
            return '未知状态';
        }
    }

    /**
     * uniqid() 根据微秒时间戳生成13位的随机字符串
     * substr(uniqid(), 7, 13) 截取7至13位的字符
     * str_split(string) 将字符串分割成数组形式
     * array_map(ord, array) 将数组的每值 * 自身并返回新的数组  例如：1*1 2*2 3*3
     * implode(array) 将数组拼接成字符串
     * substr(string, 0, 8) 截取0至8位字符
     * strtoupper($prefix) 将自定义的订单前缀转换成大写
     * date('Ymd') 年月日
     * @param $prefix
     * @return string
     * @throws \Exception
     */
    public static function getOrderNo($prefix)
    {
        return generate_order_no($prefix);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function encodeOrderForm($data)
    {
        return Yii::$app->serializer->encode($data);
    }

    public function decodeOrderForm($data)
    {
        return Yii::$app->serializer->decode($data);
    }

    public function encodeSupportPayTypes($data)
    {
        return Yii::$app->serializer->encode($data);
    }

    public function decodeSupportPayTypes($data)
    {
        return Yii::$app->serializer->decode($data);
    }

    public function getPayTypeText($payType = null)
    {
        $text = isset($this->pay_type_array[$payType]) ? $this->pay_type_array[$payType] : "";
        return $text;
    }

    /**
     * 获取配送方式文本
     * @return mixed|string
     */
    public function getSendTypeText()
    {
        $text = isset($this->send_type_array[$this->send_type]) ? $this->send_type_array[$this->send_type] : "";

        if ($this->send_type != self::SEND_TYPE_SELF && $this->send_type != self::SEND_TYPE_CITY) {
            if ($this->is_send == 1) {
                if ($this->express_no) {
                    $text = '快递发货';
                } else {
                    $text = '无需物流';
                }
            } else {
                $text = '快递发货';
            }
        }
        return $text;
    }

    /**
     * 验证发货快递公司是否正确
     * @throws \Exception
     */
    public function validateExpress($express)
    {
        $expressList = Express::getExpressList();
        $sentinel = false;
        foreach ($expressList as $value) {
            if ($value['name'] == $express) {
                $sentinel = true;
                break;
            }
        }
        if (!$sentinel) {
            throw new \Exception('快递公司错误');
        }
    }

    public function getStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'store_id']);
    }

    public function getClerkUser()
    {
        return $this->hasOne(ClerkUser::className(), ['id' => 'clerk_id']);
    }

    public function getClerk()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])
            ->via('clerkUser');
    }

    public function getOrderClerk()
    {
        return $this->hasOne(OrderClerk::className(), ['order_id' => 'id']);
    }

    public function getComments()
    {
        return $this->hasMany(OrderComments::className(), ['order_id' => 'id']);
    }

    public function getRefund()
    {
        return $this->hasMany(OrderRefund::className(), ['order_id' => 'id']);
    }

    public function getUserCards()
    {
        return $this->hasMany(UserCard::className(), ['order_id' => 'id']);
    }

    public function getMchOrder()
    {
        return $this->hasOne(MchOrder::className(), ['order_id' => 'id']);
    }

    public function getMch()
    {
        return $this->hasOne(Mch::className(), ['id' => 'mch_id']);
    }

    /**
     * 获取订单对应的插件名称
     * @return string
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    public function getSignName()
    {
        if ($this->sign == '' && $this->mch_id == 0) {
            $signName = '商城';
        } elseif ($this->mch_id > 0) {
            $signName = '多商户';
        } else {
            try {
                $signName = \Yii::$app->plugin->getPlugin($this->sign)->getDisplayName();
            } catch (\Exception $exception) {
                $signName = '未知插件';
            }
        }
        return $signName;
    }

    /**
     * 获取商品名
     * @param $data
     * @return mixed
     */
    public static function getGoodsName($data)
    {
        $wids = '';
        foreach ($data as $value) {
            if (empty($wids)) {
                $wids = $value['goods_warehouse_id'];
            } else {
                $wids .= ',' . $value['goods_warehouse_id'];
            }
        }
        if (!empty($wids)) {
            $gw_info = GoodsWarehouse::find()->where("id in ($wids)")->select('id,name')->asArray()->all();
            foreach ($data as $key => $item) {
                foreach ($gw_info as $v) {
                    if ($v['id'] == $item['goods_warehouse_id']) {
                        $data[$key]['name'] = $v['name'];
                    }
                }
            }
        }
        return $data;
    }

    public function getExpressSingle()
    {
        return $this->hasOne(OrderExpressSingle::className(), ['order_id' => 'id'])->orderBy('created_at DESC');
    }

    public function getDetailExpress()
    {
        return $this->hasMany(OrderDetailExpress::className(), ['order_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getDetailExpressRelation()
    {
        return $this->hasMany(OrderDetailExpressRelation::className(), ['order_id' => 'id'])->andWhere(['is_delete' => 0]);
    }

    public function getVipCardDiscount()
    {
        return $this->hasMany(VipCardDiscount::className(), ['order_id' => 'id']);
    }

    public function getPaymentOrder()
    {
        return $this->hasOne(PaymentOrder::className(), ['order_no' => 'order_no']);
    }

    public function getOrderActionStatus($order)
    {
        $data['is_express_send'] = 0;
        $data['is_city_send'] = 0;
        $data['is_store_send'] = 0;
        $data['is_clerk_order'] = 0;
        $data['is_finish_order'] = 0;
        $data['is_confirm_order'] = 0;
        $data['is_print'] = 0;
        $data['is_resume'] = 0;
        $data['is_destroy'] = 0;
        $data['is_remark'] = 0;
        $data['edit_express_status'] = 0;
        $data['edit_city'] = 0;
        $data['is_edit_address'] = 0;
        $data['is_coerce_cancel'] = 0;
        $data['is_recycle'] = 0;

        if ($order['is_send'] == 0 && $order['cancel_status'] != 1 && $order['is_recycle'] == 0 && $order['status'] != 0) {
            // 快递订单发货
            if ($order['is_pay'] == 1 || $order['pay_type'] == 2) {
                if ($order['send_type'] == 0 && $order['is_send_show'] == 1) {
                    $data['is_express_send'] = 1;
                }

                // 同城配送发货
                if ($order['send_type'] == 2 && $order['is_send_show'] == 1) {
                    $data['is_city_send'] = 1;
                }

                // 核销订单
                if ($order['send_type'] == 1 && $order['clerk'] == null && $order['is_clerk_show']) {
                    $data['is_clerk_order'] = 1;
                }

                // 到店自提发货
                if ($order['send_type'] == 1 && $order['is_send_show'] == 1) {
                    $data['is_store_send'] = 1;
                }
            }
        }

        // 结束订单
        if ($order['is_recycle'] == 0 && $order['is_confirm'] == 1 && $order['is_sale'] == 0 && $order['status'] != 0) {
            $data['is_finish_order'] = 1;
        }

        // 确认收货
        if ($order['is_recycle'] == 0 && $order['is_send'] == 1 && $order['is_confirm'] == 0 && $order['status'] != 0 && $order['is_confirm_show']) {
            $data['is_confirm_order'] = 1;
        }

        // 打印小票
        if ($order['is_recycle'] == 0) {
            $data['is_print'] = 1;
        }
        // 恢复订单
        // 删除订单
        if ($order['is_recycle'] == 1) {
            $data['is_resume'] = 1;
            $data['is_destroy'] = 1;
        }
        // 商家订单备注
        if ($order['is_recycle'] == 0) {
            $data['is_remark'] = 1;
        }

        // 修改物流
        if ($order['send_type'] != 2 && $order['cancel_status'] != 1 && $order['is_confirm'] == 0 && $order['is_recycle'] == 0 && $order['status'] != 0) {
            if ($order['detailExpress'] && count($order['detailExpress']) == 1 && $order['is_send'] == 1) {
                $data['edit_express_status'] = 1;
            }

            // 多个订单物流不能在订单列表修改，需在详情修改
            if ($order['detailExpress'] && count($order['detailExpress']) >= 1) {
                $data['edit_express_status'] = 2;
            }
        }

        // 修改配送员
        if ($order['send_type'] == 2 && $order['cancel_status'] != 1 && $order['is_confirm'] == 0 && $order['city_info'] && $order['is_recycle'] == 0 && $order['status'] != 0) {
            $data['edit_city'] = 1;
        }
        // 修改收货地址
        if ($order['send_type'] != 2 && $order['cancel_status'] == 0 && $order['is_send'] == 0) {
            $data['is_edit_address'] = 1;
        }

        // 强制取消
        if ($order['is_send'] == 0 && $order['cancel_status'] == 0 && $order['is_cancel_show'] == 1 && $order['status'] == 1) {
            $data['is_coerce_cancel'] = 1;
        }
        // 加入回收站
        if ($order['is_recycle'] == 0 && $order['status'] == 1) {
            $data['is_recycle'] = 1;
        }

        // TODO 电子面单

        return $data;
    }


    // 获取电子面单
    // 兼容旧数据
    public function getExpressSingleList($order)
    {
        // 电子面单列表
        $newExpressSingle = [];
        if ($order['detailExpress']) {
            foreach ($order['detailExpress'] as $deItem) {
                if ($deItem['expressSingle']) {
                    $newItem = [];
                    $newItem['express'] = $deItem['express'];
                    $newItem['send_type'] = $deItem['send_type'];// 1.快递|2.其它方式
                    $newItem['express_content'] = $deItem['express_content'];
                    $newItem['express_no'] = $deItem['express_no'];
                    $newGoodsList = [];
                    foreach ($deItem['expressRelation'] as $erItem) {
                        $newGoodsItem = [];
                        $goodsAttr = $erItem['orderDetail']['goods_info']['goods_attr'];
                        $newGoodsItem['cover_pic'] = $goodsAttr['pic_url'] ?: $goodsAttr['cover_pic'];
                        $newGoodsList[] = $newGoodsItem;
                    }
                    $newItem['goods_list'] = $newGoodsList;
                    $newItem['print_teplate'] = $deItem['expressSingle']['print_teplate'];
                    $newExpressSingle[] = $newItem;
                }
            }
        } else {
            if ($order['expressSingle']) {
                $newItem = [];
                $newItem['express'] = $order['express'];
                $newItem['express_no'] = $order['express_no'];
                $newItem['send_type'] = $order['express_no'] ? 1 : 2;
                $newItem['express_content'] = '无需物流发货';
                $newGoodsList = [];
                foreach ($order['detail'] as $dItem) {
                    $newGoodsItem = [];
                    $goodsAttr = $dItem['goods_info']['goods_attr'];
                    $newGoodsItem['cover_pic'] = $goodsAttr['pic_url'] ?: $goodsAttr['cover_pic'];
                    $newGoodsList[] = $newGoodsItem;
                }
                $newItem['goods_list'] = $newGoodsList;
                $newItem['print_teplate'] = $order['expressSingle']['print_teplate'];
                $newExpressSingle[] = $newItem;
            }
        }

        return $newExpressSingle;
    }

    /**
     * 获取订单信息
     * @param $wheres
     * @return MemberLevel|array|null
     */
    public static function getOrderInfo($wheres)
    {
        $id = isset($wheres["id"]) ? $wheres["id"] : 0;
        $orders = self::getOneData($id);
        $returnData = [];
        if (empty($orders) || $orders->is_delete == 1) {
            return [];
        }
        $result = false;
        foreach ($wheres as $key => $value) {
            $result = self::checkIsExistOrder($key, $orders, $value);
            if ($result === false) {
                break;
            }
        }
        if ($result === true) {
            $returnData = $orders;
        }
        return $returnData;
    }


    /**
     * 检测数据是否存在数组中
     * @param $key
     * @param $data
     * @param $value
     * @return bool
     */
    private static function checkIsExistOrder($key, $data, $value)
    {
        if ($value == $data->$key) {
            return true;
        }
        return false;
    }

    /**
     * 更新
     * @param $colums
     * @param $conditions
     * @return int
     */
    public static function updateOrder($colums, $conditions)
    {
        return self::updateAll($colums, $conditions);
    }


}
