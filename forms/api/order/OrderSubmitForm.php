<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单api-提交订单
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\order;

use app\component\jobs\OrderSubmitJob;
use app\core\ApiCode;
use app\core\exceptions\OrderException;
use app\events\OrderEvent;
use app\forms\common\CommonMallMember;
use app\forms\common\DeliveryCommon;
use app\forms\common\order\OrderCommon;
use app\forms\common\template\TemplateList;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\logic\CouponLogic;
use app\logic\OptionLogic;
use app\logic\UserCouponLogic;
use app\models\BaseModel;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\DistrictArr;
use app\models\Form;
use app\models\FreeDeliveryRules;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsMemberPrice;
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\MemberLevel;
use app\models\Option;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PostageRules;
use app\models\Store;
use app\models\User;
use app\models\UserAddress;
use app\models\UserCoupon;
use app\plugins\mch\models\Mch;
use app\services\Order\AttrGoodsService;
use app\services\Order\CouponService;
use app\services\Order\FullReliefPriceService;
use app\services\Order\IntegralService;
use app\services\Order\ScoreService;
use app\validators\PhoneNumberValidator;
use yii\db\Query;
use app\forms\common\mch\SettingForm;
use app\models\CommonOrderDetail;
use app\models\IntegralDeduct;
use yii\helpers\ArrayHelper;
use app\services\Order\SameGoodsService;
use app\services\wechat\WechatTemplateService;

class OrderSubmitForm extends BaseModel
{
    /** @var UserAddress */
    protected $userAddress;

    protected $sign;

    protected $supportPayTypes;

    /**
     * 是否开启会员价会员折扣功能
     * @var bool
     */
    protected $enableMemberPrice = true;

    /**
     * 是否开启优惠券功能
     * @var bool
     */
    protected $enableCoupon = true;

    /**
     * 是否开启积分功能
     * @var bool
     */
    protected $enableScore = true;

    /**
     * 是否开启购物券
     * @var bool
     */
    protected $enableIntegral= true;

    /**
     * 是否开启自定义表单功能
     * @var bool
     */
    protected $enableOrderForm = true;

    /**
     * 是否开启区域允许购买
     * @var bool
     */
    protected $enableAddressEnable = true;

    /**
     * 是否开启起送规则
     * @var bool
     */
    protected $enablePriceEnable = true;

    /**
     * 订单状态|1.已完成|0.进行中 不能对订单进行任何操作
     * @var int
     */
    public $status = 0;

    public $form_data;

    public $goodsTotalPriceService;

    public function rules()
    {
        return [
            [
                ['form_data'],
                'required'
            ],
        ];
    }

    public function getEnableCoupon()
    {
        return $this->enableCoupon;
    }

    public function getMemberPrice()
    {
        return $this->enableMemberPrice;
    }

    /**
     * 设置订单的标识，主要用于区分插件
     * @param string $sign
     * @return $this
     */
    public function setSign($sign)
    {
        $this->sign = $sign;
        return $this;
    }

    public function setUserAddress($val)
    {
        $this->userAddress = $val;
        return $this;
    }

    /**
     * 设置支持的支付方式,支付方式见readme->支付
     * @param null|array $supportPayTypes
     * @return $this
     */
    public function setSupportPayTypes($supportPayTypes = null)
    {
        $this->supportPayTypes = $supportPayTypes;
        return $this;
    }

    public function setEnableMemberPrice($val)
    {
        $this->enableMemberPrice = $val;
        return $this;
    }

    public function setEnableCoupon($val)
    {
        $this->enableCoupon = $val;
        return $this;
    }

    public function setEnableScore($val)
    {
        $this->enableScore = $val;
        return $this;
    }

    public function setEnableOrderForm($val)
    {
        $this->enableOrderForm = $val;
        return $this;
    }

    public function setEnableAddressEnable($val)
    {
        $this->enableAddressEnable = $val;
        return $this;
    }

    public function setEnablePriceEnable($val)
    {
        $this->enablePriceEnable = $val;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * 订单预览
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\db\Exception
     */
    public function toSubmitOrder()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            //$this->changeParam();
            $data = $this->handleData();
            //$data = $this->changeData($data);
        } catch (OrderException $orderException) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, CommonLogic::getExceptionMessage($orderException));
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $data);
    }

   /**
     * 提交订单
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function doSubmitOrder()
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (!$this->validate()) {
                return $this->returnApiResultData();
            }
            try {
                $data = $this->handleData($type = 2);
            } catch (OrderException $orderException) {
                \Yii::$app->redis->set('var2',$orderException);
                return $this->returnApiResultData(ApiCode::CODE_FAIL, CommonLogic::getExceptionMessage($orderException));
            }
            if (!$this->getUserAddress() && !$data['all_self_mention']) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, "请先选择收货地址");
            }
            if ($data['all_self_mention']) {
                if (!$data['user_address']['name']) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL, "请填写联系人");
                }
                if (!$data['user_address']['mobile']) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL, "请填写手机号");
                }
                /** @var Mall $mall */
                $mall   = Mall::findOne(['id' => \Yii::$app->mall->id]);
                $status = $mall->getMallSettingOne('mobile_verify');
                if ($status) {
                    $value   = $data['user_address']['mobile'];
                    $pattern = (new PhoneNumberValidator())->pattern;
                    if ($value && !preg_match($pattern, $value)) {
                        return $this->returnApiResultData(ApiCode::CODE_FAIL, "手机格式不正确");
                    }
                }
            }

            foreach ($data['list'] as $cityItem) {
                if (isset($cityItem['city']) && isset($cityItem['city']['error'])) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL, $cityItem['city']['error']);
                }
            }

            $token  = $this->getToken();

            $oldOrder = Order::findOne(['token' => $token, 'sign' => $this->sign, 'is_delete' => 0]);
            if ($oldOrder)  return $this->returnApiResultData(ApiCode::CODE_FAIL,'重复下单。');
            if (!$data['user_address_enable']) return $this->returnApiResultData(ApiCode::CODE_FAIL,'当前收货地址不允许购买。');
            if (!$data['price_enable']) return $this->returnApiResultData(ApiCode::CODE_FAIL,'订单总价未达到起送要求。');
            $user = \Yii::$app->user->identity;

            $districtArr = new DistrictArr();
            $event_data = array();//事件参数
            foreach ($data['list'] as $orderItem) {
                $order = new Order();
                $order->mall_id = \Yii::$app->mall->id;
                $order->user_id = $user->getId();
                $order->mch_id = $orderItem['mch']['id'];
                $order->order_no = Order::getOrderNo('S');;
                $order->total_price = $orderItem['total_price'];
                $order->total_pay_price = $orderItem['total_price'];
                $order->express_original_price = $orderItem['express_price'];
                $order->express_price = $orderItem['express_price'];
                $order->total_goods_price = $orderItem['total_goods_price'];
                $order->total_goods_original_price = $orderItem['total_goods_original_price'];
                $order->member_discount_price = $orderItem['member_discount'];
                $order->use_user_coupon_id = 0;
                $order->coupon_discount_price = 0;
                $order->use_score = $orderItem['score']['use'] ? $orderItem['score']['use_num'] : 0;
                //积分抵扣
                $order->score_deduction_price = $orderItem['score']['use'] ? $orderItem['score']['deduction_price'] : 0;
                //购物券抵扣
                $order->integral_deduction_price = $orderItem['integral']['use'] ? $orderItem['integral']['integral_deduction_price'] : 0;

                $order->name = $data['user_address']['name'];
                $order->mobile = $data['user_address']['mobile'];
                if ($orderItem['delivery']['send_type'] !== 'offline') {
                    $order->address = $data['user_address']['province']
                        . ' '
                        . $data['user_address']['city']
                        . ' '
                        . $data['user_address']['district']
                        . ' '
                        . $data['user_address']['detail'];

                    $order->address_id=$data['user_address']['id'];
                }

                $order->province_id = $districtArr->getId($data['user_address']['province']);
                $order->remark = empty($orderItem['remark']) ? "" : $orderItem['remark'];
                $order->order_form = $order->encodeOrderForm($orderItem['order_form_data']);
                $order->distance = isset($orderItem['form_data']['distance']) ? $orderItem['form_data']['distance'] : 0;//同城距离
                $order->words = '';

                $order->is_pay = Order::IS_PAY_NO;
                $order->pay_type = Order::IS_PAY_NO;
                $order->is_send = 0;
                $order->is_confirm = Order::IS_COMMENT_NO;
                $order->is_sale = 0;
                $order->support_pay_types = $order->encodeSupportPayTypes($this->supportPayTypes);
                if ($orderItem['delivery']['send_type'] === 'offline') {
                    if (empty($orderItem['store'])) return $this->returnApiResultData(ApiCode::CODE_FAIL,'请选择自提门店。');
                    $order->store_id = $orderItem['store']['id'];
                    $order->send_type = Order::SEND_TYPE_SELF;
                } elseif ($orderItem['delivery']['send_type'] === 'city') {
                    $order->distance = $orderItem['distance'];
                    $order->location = $data['user_address']['longitude'] . ',' . $data['user_address']['latitude'];
                    $order->send_type = Order::SEND_TYPE_CITY;
                    $order->store_id = 0;
                } else {
                    $order->send_type = Order::SEND_TYPE_EXPRESS;
                    $order->store_id = 0;
                }
                $order->sign = $this->sign !== null ? $this->sign : '';
                $order->token = $token;
                $order->status = $this->status;

                //满减金额
                $order->full_relief_price = $orderItem['total_full_relief_price'];

                if (!$order->save()) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,(new BaseModel())->responseErrorMsg($order));
                }

                if ($orderItem['mch']['id'] > 0) {
                    // $mchOrder = new MchOrder();
                    // $mchOrder->order_id = $order->id;
                    // $res = $mchOrder->save();
                    // if (!$res) {
                    //     throw new \Exception('多商户订单创建失败');
                    // }
                }

                foreach ($orderItem['goods_list'] as $goodsItem) {
                    $this->subGoodsNum($goodsItem['goods_attr'], $goodsItem['num'], $goodsItem);
                    $this->extraOrderDetail($order, $goodsItem);
                }

                // 优惠券标记已使用(此段代码没有用)
                if ($order->use_user_coupon_id) {
                    $userCoupon = UserCoupon::findOne($order->use_user_coupon_id);
                    $userCoupon->is_use = 1;
                    $userCoupon->is_failure = 1;
                    if ($userCoupon->update(true, ['is_use']) === false) {
                        return $this->returnApiResultData(ApiCode::CODE_FAIL,'优惠券状态更新失败。');
                    }
                }

                // 扣除积分
                if ($order->score_deduction_price) {
                    $userScoreInfo = User::getUserWallet(\Yii::$app->user->id);
                    $userScoreCard = empty($userScoreInfo) ? 0 : number_format($userScoreInfo['static_score']+$userScoreInfo['dynamic_score']);
                    $userScore = empty($userScoreInfo) ? 0 : $userScoreInfo['score'];
//                    $userScore >= $userScoreCard
                    if(!empty($userScore)){ 
                        if (!\Yii::$app->currency->setUser($user)->score->sub($order->use_score, '下单积分抵扣')) {
                            return $this->returnApiResultData(ApiCode::CODE_FAIL,'积分操作失败。');
                        }else{
                            $resx = IntegralDeduct::buyGooodsDeduct($order,0);
                            if($resx === false){
                                return $this->returnApiResultData(ApiCode::CODE_FAIL,'积分券扣除失败。xxx');
                            }
                        }
                    }
                }
                

                // 扣除购物券
                if ($order->integral_deduction_price) {
                    $res = IntegralDeduct::buyGooodsDeduct($order,1);
                    if($res === false) return $this->returnApiResultData(ApiCode::CODE_FAIL,'购物券扣除失败。');
                }
                //开放额外的订单处理接口
                $this->extraOrder($order, $orderItem);
                // 购物车ID
                $cartIds = [];
                foreach ($orderItem['form_data']['goods_list'] as $goodsItem) {
                    if (isset($goodsItem["cart_id"]) && $goodsItem["cart_id"] > 0) {
                        $cartIds[] = $goodsItem['cart_id'];
                    }
                }
                $event = new OrderEvent();
                $event->order = $order;
                $event->sender = $this;
                $event->cartIds = $cartIds;
                $event->formData = $data['form_data'];
                $event->pluginData = ['sign' => 'vip_card', 'vip_discount' => $orderItem['vip_discount'] ?? null];
                $event->orderItem = $orderItem;


                array_push($event_data,$event); //保存事件参数,待入库后触发

                //添加公共订单任务
                $commonOrderForm = new CommonOrderForm();
                // 不走队列
                // $commonOrderForm->commonOrderJob($order->id, CommonOrderDetail::STATUS_NORMAL, CommonOrderDetail::TYPE_MALL_GOODS, $order->mall_id, $order->user_id, $order->total_pay_price);
                $commonOrderForm->createCommonOrder($order->id, CommonOrderDetail::STATUS_NORMAL, CommonOrderDetail::TYPE_MALL_GOODS, $order->mall_id, $order->user_id, $order->total_pay_price);
            }
            $t->commit();
            //遍历触发事件

            foreach ($event_data as $event) {
                \Yii::$app->trigger(Order::EVENT_CREATED, $event);
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", [
                'token'    => $token,
                'queue_id' => $queueId ?? 0,
            ]);
        }catch(\Exception $e){
            $t->rollBack();
            \Yii::$app->redis->set('var1',$e -> getMessage());
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }

    /** xuyaoxiang 10/15
     * 处理数据
     * @param $type 1预览订单2提交订单
     * @return array
     * @throws OrderException
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\db\Exception
     */
    public function handleData($type = 1)
    {

        $listData = $this->getListData($this->form_data["list"]);

        foreach ($listData as &$item) {
            $goods_list = $item['goods_list'];
            $this->checkGoodsStock($goods_list);

            $this->checkGoodsOrderLimit($goods_list);

            $this->checkGoodsBuyLimit($goods_list);

            $formDataItem            = $item['form_data'];
            $item['express_price']   = price_format(0);
            $item['remark']          = isset($formDataItem['remark'])
                ? $formDataItem['remark'] : null;
            $item['order_form_data'] = isset($formDataItem['order_form'])
                ? $formDataItem['order_form'] : null;
            $totalGoodsPrice         = 0;
            $totalGoodsOriginalPrice = 0;

            foreach ($goods_list as $i => $goodsItem) {
                $totalGoodsPrice          += $goodsItem['total_price'];
                $totalGoodsOriginalPrice  += $goodsItem['total_original_price'];
            }

            unset($goodsItem);
            $item['goods_list']                 = $goods_list;
            $item['total_goods_price']          = price_format($totalGoodsPrice);
            $item['total_goods_original_price'] = price_format($totalGoodsOriginalPrice);

            //找出多规格同一商品，区分规格商品列表合并成同一商品列表
            $SameGoodsService = new SameGoodsService($item);
            $item             = $SameGoodsService->getSameGoods();

            //会员价减免
            $item = $this->setMemberDiscountData($item);

            //满额减免
            $FullReliefPriceService = new FullReliefPriceService($item);
            $item                   = $FullReliefPriceService->countFullRelief();

            //优惠券开始
            $CouponService = new CouponService($item, $type, $this->enableCoupon);
            $CouponService->setFormData($item['form_data']);
            //获取当前商品用户可用优惠卷列表
            $item = $CouponService->getUserGoodsCouponList();
            //检查是否合法优惠卷,匹配商品和用户
            $item = $CouponService->getUsableUserCouponId();
            //优惠卷计算
            $item = $this->setCouponDiscountData($item, $formDataItem, $type);
            //优惠卷结束

            //是否使用积分减免
            if (isset($this->form_data['use_score']) && $this->form_data['use_score'] == 1) {
                $use_score = true;
            } else {
                $use_score = false;
            }

            //是否使用购物券
            if (isset($this->form_data['use_integral']) && $this->form_data['use_integral'] == 1) {
                $use_integral = true;
            } else {
                $use_integral = false;
            }

            //计算积分
            $ScoreService = new ScoreService($item, $type, $use_score, $this->enableScore);
            $item         = $ScoreService->countScore();

            //计算购物券总额
            $user_integral   = User::getCanUseIntegral(\Yii::$app->user->id);
            $IntegralService = new IntegralService($item, $user_integral, $type, $use_integral, $this->enableIntegral);
            $item            = $IntegralService->countIntegral();

            //same_goods_list转换到goods_list入库
            $AttrGoodsService        = new AttrGoodsService($item);
            $item                    = $AttrGoodsService->getGoodsList();
            $item['same_goods_list'] = $SameGoodsService->toArray($item['same_goods_list']);

            $item['total_price'] = $item['total_goods_price'];

            $item = $this->setDeliveryData($item, $formDataItem);

            $item                = $this->setExpressData($item);

            $totalPrice          = price_format($item['total_goods_price'] + $item['express_price']);
            $item['total_price'] = $this->setTotalPrice($totalPrice);

            $item = $this->setGoodsForm($item);
        }

        $total_price        = 0;
        $totalOriginalPrice = 0;
        foreach ($listData as &$priceItem) {
            $total_price        += $priceItem['total_price'];
            $totalOriginalPrice += $priceItem['total_goods_original_price'];
        }
        //是否自提
        $isSelfMention = false;
        foreach ($listData as &$item) {
            if (isset($item['delivery']) && $item['delivery']['send_type'] == 'offline') {
                $isSelfMention = true;
                break;
            }
        }

        $allSelfMention = true;
        foreach ($listData as &$item) {
            if (isset($item['delivery']) && $item['delivery']['send_type'] != 'offline') {
                $allSelfMention = false;
                break;
            }
        }

        $userAddress = $this->getUserAddress();

        $addressEnable = true;
        foreach ($listData as &$item) { // 检查区域允许购买
            $addressEnable = $this->getUserAddressEnable($userAddress, $item);
            if ($addressEnable == false) {
                break;
            }
        }
        //下单判断，用户收获地址是否在配送范围内
        if ($addressEnable == false) {
            throw new OrderException('当前收货地址不允许购买。');
        }

        $priceEnable = true;
        if ($allSelfMention) {
            $priceEnable = true;
        } else {
            foreach ($listData as &$item) { // 检查是否达到起送规则
                $priceEnable = $this->getPriceEnable(price_format($item['total_goods_original_price']), $userAddress, $item);
                if ($priceEnable == false) {
                    break;
                }
            }
        }

        if ($priceEnable == false) {
            throw new OrderException('订单总价未达到起送要求。');
        }

        // 获取上一次的自提订单
        if ($allSelfMention) {
            if (!$userAddress) {
                /** @var Order $order */
                $order = Order::find()->where([
                    'user_id'   => \Yii::$app->user->id,
                    'send_type' => Order::SEND_TYPE_SELF,
                    'is_delete' => 0
                ])->orderBy(['created_at' => SORT_DESC])->one();
                if ($order) {
                    $userAddress = [
                        'name'   => $order->name,
                        'mobile' => $order->mobile
                    ];
                }
            }
            // 下单预览记住用户更改的自提信息
            if (isset($this->form_data['user_address'])) {
                $userAddress = $this->form_data['user_address'];
            }
        }
        $hasCity = false;
        foreach ($listData as &$item) {
            if (isset($item['delivery']) && $item['delivery']['send_type'] == 'city') {
                $hasCity = true;
                break;
            }
        }

        foreach ($listData as &$item) {
            $this->afterGetAllItem($item);
        }

        $userCouponList = [];
        foreach ($listData as &$item) {
            $userCouponList[] = $this->getUserUsableCouponToOrder($item);
        }

        //获取积分开启状态
        //商户号
        $mch_id = isset($this->form_data['list']['mch_id']) ? $this->form_data['list']['mch_id'] : 0;
        //获取积分开启状态
        $optionCache = OptionLogic::get(
            Option::NAME_PAYMENT,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            '',
            $mch_id
        );

        if ($this->enableScore) {
            $score_enable = $optionCache->score_status;
        } else {
            $score_enable = false;
        }
        //购物券开关
        if ($this->enableIntegral) {
            $integral_enable = isset($optionCache->integral_status)?$optionCache->integral_status:false;
        } else {
            $integral_enable = false;
        }

        $is_auth_phone = 1;
        if (!OrderCommon::checkIsBindMobile()) {
            $is_auth_phone = 0;
        }
        return [
            'list'                => $listData,
            'total_price'         => price_format($total_price),
            'user_coupon'         => $userCouponList,
            'is_auth_phone'       => $is_auth_phone,
            'price_enable'        => $priceEnable,
            'user_address'        => $hasCity ? (($userAddress && $userAddress->longitude && $userAddress->latitude) ? $userAddress : []) : $userAddress,
            'user_address_enable' => $addressEnable,
            'is_self_mention'     => $isSelfMention,
            'custom_currency_all' => $this->getcustomCurrencyAll($listData),
            'all_self_mention'    => $allSelfMention,
            'hasCity'             => $hasCity,
            'score_enable'        => $score_enable,
            'integral_enable'     => $integral_enable, //购物券
            'form_data'           => [
                'sign'             => isset($this->form_data['sign'])?$this->form_data['sign']:null,
                'related_id'       => isset($this->form_data['related_id'])?$this->form_data['related_id']:null,
                'related_user_id' => isset($this->form_data['related_user_id'])?$this->form_data['related_user_id']:null,
            ],
        ];
    }

    /**
     *
     * @param $mchItem
     * @return mixed
     */
    public function afterGetAllItem(&$mchItem)
    {
        return $mchItem;
    }

    /**
     * 获取传过来的商品列表（考虑从购物车跳转过来，有多商品情况）
     * @param $formDataList
     * @return array [ ['mch' => '商户信息', 'goods_list' => 'array 订单的商品信息和金额列表'] ]
     * @throws OrderException
     */
    protected function getListData($formDataList)
    {
        foreach ($formDataList as $i => $formDataItem) {
            $mchItem    = [
                'mch'        => $this->getMchInfo(isset($formDataItem['mch_id']) ? $formDataItem['mch_id'] : 0),
                'goods_list' => $this->getGoodsListData($formDataItem['goods_list']),
                'form_data'  => $formDataItem,
            ];
            $listData[] = $mchItem;
        }
        return $listData;
    }

    /**
     * 获取商户信息
     * @param $id
     * @return array
     */
    protected function getMchInfo($id)
    {
        if ($id == 0) {
            return [
                'id'   => 0,
                'name' => \Yii::$app->mall->name,
            ];
        } else {
            $mch = Mch::findOne($id);
            \Yii::$app->setMchId($mch->id);
            return [
                'id'   => $id,
                'name' => $mch ? $mch->store->name : '未知商户',
            ];
        }
    }

    /**
     * 商品列表数据
     * @param $goodsList
     * @return array
     * @throws OrderException
     */
    protected function getGoodsListData($goodsList)
    {
        $list = [];
        foreach ($goodsList as $i => $goodsItem) {
            $result              = $this->getOneGoodsItemData($goodsItem);
            $result['form_data'] = isset($goodsItem['form_data']) ? $goodsItem['form_data'] : null;
            $list[]              = $result;
        }
        return $list;
    }

    /**
     * 单个商品数据
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @param $goodsItem
     * @return array
     * @throws OrderException
     */
    protected function getOneGoodsItemData($goodsItem)
    {
        /** @var Goods $goods */
        $goods = Goods::find()->with('goodsWarehouse')->where([
            'id'        => $goodsItem['id'],
            'mall_id'   => \Yii::$app->mall->id,
            'status'    => 1,
            'is_delete' => 0,
        ])->one();

        if (!$goods) {
            throw new OrderException('商品不存在或已下架。');
        }

        // 其他商品特有判断
        $this->checkGoods($goods, $goodsItem);
        try {
            /** @var OrderGoodsAttr $goodsAttr */
            $goods_attr_id     = $goodsItem['goods_attr_id'];
            $goodsAttr         = $this->getGoodsAttr($goods_attr_id, $goods);
            $goodsAttr->number = $goodsItem['num'];
        } catch (\Exception $exception) {
            throw new OrderException($exception->getFile() . ";line:" . $exception->getLine() . ";message:" . $exception->getMessage() . '无法查询商品`' . $goods->name . '`的规格信息。');
        }
        $attrList = $goods->signToAttr($goodsAttr->sign_id);
        $itemData = [
            'id'                   => $goods->id,
            'name'                 => $goods->goodsWarehouse->name,
            'num'                  => $goodsItem['num'],
            'forehead_score'       => $goods->forehead_score,
            'forehead_score_type'  => $goods->forehead_score_type,
            'accumulative'         => $goods->accumulative,
            'pieces'               => $goods->pieces,
            'forehead'             => $goods->forehead,
            'freight_id'           => $goods->freight_id,
            'unit_price'           => price_format($goodsAttr->original_price),
            'total_original_price' => price_format($goodsAttr->original_price * $goodsItem['num']),
            'total_price'          => price_format($goodsAttr->price * $goodsItem['num']),
            'member_discount'      => price_format(0),
            'cover_pic'            => $goods->goodsWarehouse->cover_pic,
            'is_level_alone'       => $goods->is_level_alone,
            'is_level'             => $goods->is_level,
            'goods_warehouse_id'   => $goods->goods_warehouse_id,
            'sign'                 => $goods->sign,
            'confine_order_count'  => $goods->confine_order_count,
            'form_id'              => $goods->form_id,
            'goods_attr'           => $goodsAttr,
            'attr_list'            => $attrList,
            'discounts'            => $goodsAttr->discount,
            'user_coupon_id'       => isset($goodsItem["user_coupon_id"]) ? $goodsItem["user_coupon_id"] : 0,
            'fulfil_price'         => $goods->fulfil_price,
            'full_relief_price'    => $goods->full_relief_price,
            'max_deduct_integral'  => $goods->max_deduct_integral
            // 规格自定义货币 例如：步数宝的步数币
            //'custom_currency' => $this->getCustomCurrency($goods, $goodsAttr),

        ];
        return $itemData;
    }

    /**
     * 会员优惠（会员价和会员折扣）
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @param $discountItem
     * @return mixed
     * @throws OrderException
     */
    protected function setMemberDiscountData($discountItem)
    {

        $discountItem['member_discount'] = price_format(0);

        if (!$this->enableMemberPrice) {
            return $discountItem;
        }

        /** @var User $user */
        $user = \Yii::$app->user->identity;

        $member = MemberLevel::getOneData([
            'level'     => $user->level,
            'mall_id'   => \Yii::$app->mall->id,
            'is_delete' => 0,
        ]);
        if (!$member) {
            return $discountItem;
        }

        $totalSubPrice = 0; // 会员总计优惠金额
        foreach ($discountItem['same_goods_list'] as &$same_goods) {
            $same_goods_total_sub_price = 0; // 会员总计优惠金额
            foreach ($same_goods['goods_list'] as &$goodsItem) {

                $goodsItem['original_unit_price'] = $goodsItem['unit_price'];

                if ($goodsItem['is_level'] != 1) {
                    continue;
                }
                $memberUnitPrice = null;
                $discountName    = null;

                $goodsItem['member_discount'] = price_format(0);

                /* @var OrderGoodsAttr $goodsAttr */
                $goodsAttr = $goodsItem['goods_attr'];
                try {
                    $goodsMemberPrice = $this->getGoodsAttrMemberPrice($goodsAttr, $member->level);
                } catch (\Exception $e) {
                    throw new OrderException($e->getMessage());
                }
                if ($goodsMemberPrice && $goodsItem['is_level_alone'] == 1) {
                    $memberUnitPrice = $goodsMemberPrice;
                    if (!is_numeric($memberUnitPrice) || $memberUnitPrice < 0) {
                        throw new OrderException('商品会员价`' . $memberUnitPrice . '`不合法，会员价必须是数字且大于等于0元。');
                    }
                    $discountName = '会员价优惠';
                } elseif ($member->discount) {
                    if (!($member->discount >= 0.1 && $member->discount <= 10)) {
                        throw new OrderException('会员折扣率不合法，会员折扣率必须在1折~10折。');
                    }
                    $goodsPrice = $goodsAttr->original_price;
                    if (!is_numeric($goodsPrice) || $goodsPrice < 0) {
                        throw new OrderException('商品金额不合法，商品金额必须是数字且大于等于0元。');
                    }
                    $memberUnitPrice = $goodsPrice * $member->discount / 10;
                    $discountName    = '会员折扣优惠';
                }

                if ($memberUnitPrice && is_numeric($memberUnitPrice) && $memberUnitPrice >= 0) {
                    $goodsItem['change_unit_price'] = $memberUnitPrice;

                    $goodsAttr->member_price = $memberUnitPrice;
                    // 商品单件价格（会员优惠后）
                    $goodsAttr->price = $memberUnitPrice - ($goodsAttr->original_price - $goodsAttr->price);
                    $memberTotalPrice = price_format($memberUnitPrice * $goodsItem['num']);
                    $memberSubPrice   = $goodsItem['total_original_price'] - $memberTotalPrice;
                    if ($memberSubPrice != 0) {
                        // 减去会员优惠金额
                        $memberSubPrice                    = min($goodsItem['total_price'], $memberSubPrice);
                        $goodsItem['total_price']          = price_format($goodsItem['total_price'] - $memberSubPrice);

                        //1计算
                        $totalSubPrice                     += $memberSubPrice;
                        $same_goods_total_sub_price += $memberSubPrice;
                        $goodsItem['discounts'][]          = [
                            'name'  => $discountName,
                            'value' => $memberSubPrice > 0 ?
                                ('-' . price_format($memberSubPrice))
                                : ('+' . price_format(0 - $memberSubPrice))
                        ];

                        //2计算
                        $discountItem['total_goods_price'] = price_format($discountItem['total_goods_price'] - $memberSubPrice);
                        $same_goods['total_price'] = $same_goods['total_price'] - $memberSubPrice;
                        $goodsItem['member_discount']      = price_format($memberSubPrice);
                    }
                }
            }

            if ($same_goods_total_sub_price) {
                $same_goods['member_discount'] = price_format($same_goods_total_sub_price);
            }else{
                $same_goods['member_discount']=0;
            }
        }

        if ($totalSubPrice) {
            $discountItem['member_discount'] = price_format($totalSubPrice);
        }

        return $discountItem;
    }

    /**
     * 获取规格指定的会员等级的会员价
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 18:33
     * @param OrderGoodsAttr $goodsAttr
     * @param $memberLevel
     * @return GoodsMemberPrice|null
     * @throws \Exception
     */
    protected function getGoodsAttrMemberPrice($goodsAttr, $memberLevel)
    {
        $goodsMemberPrice = CommonMallMember::getGoodsAttrMemberPrice($goodsAttr->goodsAttr, $memberLevel);
        // $goodsMemberPrice 有可能为空
        return $goodsMemberPrice ? $goodsMemberPrice->price : null;
    }

    /**
     * 优惠券优惠
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 18:33
     * @param $couponItem
     * @param $formItem
     * @return mixed
     * @throws OrderException
     */
    protected function setCouponDiscountData($couponItem, $formItem,$type)
    {
        $returnData         = [];
        //$returnItem         = $couponItem["goods_list"];
        $returnItem         = $couponItem["same_goods_list"];
        $discountMoney      = $subPrice = 0;
        $allTotalGoodsPrice = 0;

        foreach ($returnItem as &$val) {
            $userCouponId  = $val["usable_user_coupon_id"];
            $val['coupon'] = [
                'enabled'         => true,
                'use'             => false,
                'coupon_discount' => price_format(0),
                'user_coupon_id'  => 0,
                'coupon_error'    => "",
            ];
            if (!$this->enableCoupon || $couponItem['mch']['id'] != 0) { // 入住商不可使用优惠券
                $val['coupon']['enabled'] = false;
                continue;
            }

            if (empty($userCouponId)) {
                continue;
            }

            $nowDateTime = time();
            /** @var UserCoupon $userCoupon */
            $userCoupon = UserCoupon::find()->where([
                'AND',
                ['id' => $userCouponId],
                ['user_id' => \Yii::$app->user->identity->getId()],
                ['is_delete' => 0],
                ['is_use' => 0],
                [
                    '<=',
                    'begin_at',
                    $nowDateTime
                ],
                [
                    '>=',
                    'end_at',
                    $nowDateTime
                ],
            ])->one();
            if (!$userCoupon) {
                $val['coupon']['coupon_error'] = '优惠券不存在';
                continue;
            }

            /** @var Coupon $coupon */
            $coupon = Coupon::getOneData([
                'id' => $userCoupon->coupon_id,
            ]);

            if (!$coupon) {
                $val['coupon']['coupon_error'] = '优惠券不存在';
                continue;
            }

            if ($coupon->appoint_type == Coupon::APPOINT_TYPE_CAT || $coupon->appoint_type == Coupon::APPOINT_TYPE_GOODS) {
                if ($coupon->appoint_type == Coupon::APPOINT_TYPE_CAT) { // 指定分类可用
                    $couponCatRelations = CouponCatRelation::findAll([
                        'coupon_id' => $coupon->id,
                        'is_delete' => 0,
                    ]);
                    $catIdList          = [];
                    foreach ($couponCatRelations as $couponCatRelation) {
                        $catIdList[] = $couponCatRelation->cat_id;
                    }
                    /** @var GoodsCatRelation[] $goodsCatRelations */
                    $goodsCatRelations = GoodsCatRelation::find()
                        ->select('gcr.goods_warehouse_id')
                        ->alias('gcr')
                        ->leftJoin(['gc' => GoodsCats::tableName()], 'gcr.cat_id=gc.id')
                        ->where([
                            'gc.is_delete'  => 0,
                            'gcr.cat_id'    => $catIdList,
                            'gcr.is_delete' => 0
                        ])
                        ->all();
                    $couponGoodsIdList = [];
                    foreach ($goodsCatRelations as $goodsCatRelation) {
                        $couponGoodsIdList[] = $goodsCatRelation->goods_warehouse_id;
                    }
                } else { // 指定商品可用
                    $couponGoodsRelations = CouponGoodsRelation::findAll([
                        'coupon_id' => $coupon->id,
                        'is_delete' => 0,
                    ]);
                    $couponGoodsIdList    = [];
                    foreach ($couponGoodsRelations as $couponGoodsRelation) {
                        $couponGoodsIdList[] = $couponGoodsRelation->goods_warehouse_id;
                    }
                }

                if ($userCoupon->coupon_min_price > $val['total_original_price']) { // 可用的商品原总价未达到优惠券使用条件
                    $val['coupon']['coupon_error'] = '所选优惠券未满足使用条件.';
                    continue;
                }
                $sub      = UserCouponLogic::getDiscountAmount($userCoupon, $val['total_original_price']);
                $subPrice = min($val['total_price'], $sub, $val['total_original_price']);
                if ($subPrice > 0) {
                    //$val['total_price'] = price_format($val['total_price'] - $subPrice);
                    $val['coupon']['use']             = true;
                    $val['coupon']['user_coupon_id']  = $userCoupon->id;
                    $val['coupon']['coupon_discount'] = price_format($subPrice);
                }
                $val = $this->setDiscountPrice($val, $val["total_price"], $subPrice, $couponGoodsIdList);
            } elseif ($coupon->appoint_type == 3) { // 全商品通用
                if ($val['total_price'] <= 0) { // 价格已优惠到0不再使用优惠券
                    $val['coupon']['coupon_error'] = '商品价格已为0无法使用优惠券';
                    continue;
                }

                if ($val['total_original_price'] < $userCoupon->coupon_min_price) { // 商品原总价未达到优惠券使用条件
                    $val['coupon']['coupon_error'] = '所选优惠券未满足使用条件..';
                    continue;
                }
                $subPrice = $sub = UserCouponLogic::getDiscountAmount($userCoupon, $val["total_price"]);
                if ($subPrice > $couponItem['total_goods_price']) {
                    $subPrice = $couponItem['total_goods_price'];
                }

                $subPrice = min($val['total_price'], $subPrice);

                $val['coupon']['use']             = true;
                $val['coupon']['user_coupon_id']  = $userCoupon->id;
                $val['coupon']['coupon_discount'] = price_format($subPrice);

               // $val                              = $this->setDiscountPrice($val, $val["total_price"], $subPrice);
            }

            $discountMoney += $subPrice;

            //计算单个规格商品总价,优惠比例;
            foreach ($val['goods_list'] as $goods_list_key => $goods_item) {
                $val['goods_list'][$goods_list_key]['total_price'] = SameGoodsService::countAttrGoodsList($goods_item['total_price'], $goods_item['total_price_percent'], $subPrice);
                $val['goods_list'][$goods_list_key]['coupon_discount_price'] = price_format($goods_item['total_price_percent'] * $subPrice);
                $val['goods_list'][$goods_list_key]['user_coupon_id'] = $userCouponId;
            }
        }
        //优惠券总共抵扣多少钱
        $couponItem["same_goods_list"]            = $returnItem;

        $couponItem["total_goods_price"]     = price_format($couponItem['total_goods_price'] - $discountMoney);
        $couponItem['total_coupon_discount'] = $subPrice;

        \Yii::warning('order_submit setCouponDiscountData couponItem=' . var_export($couponItem, true));
        return $couponItem;
    }

    /**
     * 积分抵扣
     * @param $scoreItem
     * @param $formMchItem
     * @param $type 1预览订单 2提交订单
     * @return mixed
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    protected function setScoreDiscountData($scoreItem, $formMchItem, $type = 1)
    {
        \Yii::warning("setScoreDiscountData formMchItem=" . var_export($formMchItem, true));
        $scoreItem['score'] = [
            'can_use'         => false,
            'use'             => false,
            'use_num'         => 0,
            'deduction_price' => price_format(0),
        ];
        if (!$this->enableScore || $scoreItem['mch']['id'] != 0) {
            return $scoreItem;
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;

        $userscore = \Yii::$app->currency->setUser($user)->score->select();

        if (!$userscore || $userscore < 0) {
            return $scoreItem;
        }
        if ($scoreItem['total_goods_price'] <= 0) {
            return $scoreItem;
        }
        $memberScoreArray = AppConfigLogic::getPaymentConfig();
        if (empty($memberScoreArray)) {
            return $scoreItem;
        }
        $memberscore  = 0;
        $score_status = $memberScoreArray["score_status"];

        if ($score_status == 1) {
            $memberscore = $memberScoreArray["score_price"];
        }


        if (!$memberscore || !is_numeric($memberscore) || $memberscore <= 0) {
            return $scoreItem;
        }

        // 积分最多可抵扣的金额
        $maxDeductionscore = min(
            intval($scoreItem['total_goods_price'] * $memberscore),
            $userscore
        );

        $totalDeductionPrice = 0; // 已抵扣的金额总和
        $totalDeductionscore = 0; // 抵扣积分总额
        foreach ($scoreItem['goods_list'] as &$goodsItem) {
            //积分抵扣金额
            if (is_nan($goodsItem['forehead_score']) || $goodsItem['forehead_score'] <= 0) {
                continue;
            }
            //单个商品最大抵扣金额
            $unitGoodsMaxDeductionPrice = 0;
            if ($goodsItem['forehead_score_type'] == 1) { // 固定方式抵扣
                $unitGoodsMaxDeductionPrice = $goodsItem['forehead_score'];
            } elseif ($goodsItem['forehead_score_type'] == 2) { // 最大百分比方式抵扣
                if ($goodsItem['forehead_score'] > 100) {
                    continue;
                }
                if (isset($goodsItem['goods_attr']['member_price'])) {
                    $unitGoodsMaxDeductionPrice = $goodsItem['goods_attr']['member_price'] * $goodsItem['forehead_score'] / 100;
                } else {
                    $unitGoodsMaxDeductionPrice = $goodsItem['unit_price'] * $goodsItem['forehead_score'] / 100;
                }
            }

            if ($goodsItem['accumulative'] == 1) { // 允许多件累计抵扣
                $goodsMaxDeductionPrice = price_format($unitGoodsMaxDeductionPrice * $goodsItem['num']);
            } else { // 只允许抵扣一件
                $goodsMaxDeductionPrice = $unitGoodsMaxDeductionPrice;
            }
            // 抵扣金额不能超过商品金额
            $goodsMaxDeductionPrice = min($goodsMaxDeductionPrice, $goodsItem['total_price']);

            $goodsMaxDeductionscore = price_format($goodsMaxDeductionPrice * $memberscore);
            /* @var OrderGoodsAttr $orderGoodsAttr */
            $orderGoodsAttr = $goodsItem['goods_attr'];

            if (($totalDeductionscore + $goodsMaxDeductionscore) > $maxDeductionscore) { // 抵扣的金额超过最多可抵扣的
                $orderGoodsAttr->use_score   = price_format($maxDeductionscore - $totalDeductionscore);
                $orderGoodsAttr->score_price = price_format($orderGoodsAttr->use_score / $memberscore);
                $totalDeductionPrice         = price_format($maxDeductionscore / $memberscore);
                $totalDeductionscore         = $maxDeductionscore;
                break;
            } else {

                $goodsMaxDeductionPrice = price_format($goodsMaxDeductionscore / $memberscore);

                $totalDeductionPrice         += price_format($goodsMaxDeductionPrice);

                $totalDeductionscore         += price_format($goodsMaxDeductionscore);
                $orderGoodsAttr->use_score   = price_format($goodsMaxDeductionscore);
                $orderGoodsAttr->score_price = price_format($goodsMaxDeductionPrice);
            }
        }

        $scoreItem['score']['use_num']         = $totalDeductionscore;
        $scoreItem['score']['deduction_price'] = price_format($totalDeductionPrice);
        $scoreItem['score']['can_use']         = $scoreItem['score']['use_num'] > 0 ? true : false;

        if (isset($this->form_data['use_score']) && $this->form_data['use_score'] == 1) {
            $scoreItem['score']['use'] = true;
        } else {
            $scoreItem['score']['use'] = false;
        }

        if ($type == 2) {
            //if ($scoreItem['score']['use']) {
             //   $scoreItem['total_goods_price'] = price_format($scoreItem['total_goods_price'] - $totalDeductionPrice);
            //}
            $scoreItem = $this->setGoodsListScoreSub($scoreItem);
        }

        return $scoreItem;
    }

    /**
     * 设置订单里每个商品被基本抵扣后的total_price字段
     * @param $scoreItem
     * @return mixed
     */
    private function setGoodsListScoreSub($scoreItem)
    {
        if (
            empty($scoreItem['score'])
            || !$scoreItem['score']['use']
            || !isset($scoreItem['score']['deduction_price'])
            || $scoreItem['score']['deduction_price'] <= 0
        ) {
            return $scoreItem;
        }

        // 排序
        uasort($scoreItem['goods_list'], function ($a, $b) {
            if ($a['total_price'] == $b['total_price']) {
                return 0;
            }
            return ($a['total_price'] < $b['total_price']) ? -1 : 1;
        });

        $deductionPrice = $scoreItem['score']['deduction_price'];
        \Yii::warning("setGoodsListScoreSub deductionPrice={$deductionPrice}");
        foreach ($scoreItem['goods_list'] as &$goodsItem) {
            if ($deductionPrice <= 0) {
                break;
            }
            if ($goodsItem['total_price'] <= 0) {
                continue;
            }
            if ($goodsItem['forehead_score_type'] == 1) { // 固定方式抵扣
                $goodsMaxSubPrice = $goodsItem['forehead_score'] * $goodsItem['num'];
            } else { // 最大百分比方式抵扣
                if ($goodsItem['forehead_score'] > 100) {
                    $goodsMaxSubPrice = 0;
                } else {
                    $goodsMaxSubPrice = $goodsItem['unit_price'] * $goodsItem['forehead_score'] * $goodsItem['num'] / 100;
                }
            }
            $maxSubPrice = min($goodsItem['total_price'], $deductionPrice, $goodsMaxSubPrice);
            \Yii::warning("setGoodsListScoreSub maxSubPrice={$maxSubPrice}");
            $deductionPrice = $deductionPrice - $maxSubPrice;
            \Yii::warning("setGoodsListScoreSub deductionPrice2={$deductionPrice}");
            \Yii::warning("setGoodsListScoreSub goodsItem[total_price]=" . $goodsItem['total_price']);
            $goodsItem['total_price'] = price_format($goodsItem['total_price'] - $maxSubPrice);
        }
        unset($goodsItem);
//        dd($deductionPrice, 0);
//        dd($mchItem['goods_list']);
        return $scoreItem;
    }

    /**
     * 获取配送方式
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:13
     * @return array 数组形式['express','offline','city']；express--快递、offline--自提、city--同城配送
     * @throws \Exception
     */
    protected function getSendType($sendItem)
    {
        if (isset($sendItem['mch']['id']) && $sendItem['mch']['id'] > 0) {
            $form         = new SettingForm();
            $form->mch_id = $sendItem['mch']['id'];
            $setting      = $form->search();

            return $setting['send_type'];
        }
        $sendType = \Yii::$app->mall->getMallSettingOne('send_type');
        if (empty($sendType)) {
            $sendType[] = 'express';
        }
        return $sendType;
    }

    /**
     * 配送方式
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @param $deliveryItem
     * @param $formMchItem
     * @return mixed
     * @throws OrderException
     * @throws \Exception
     */
    protected function setDeliveryData($deliveryItem, $formMchItem)
    {
        $sendType = $this->getSendType($deliveryItem);
        if (!isset($formMchItem['send_type'])
            || $formMchItem['send_type'] === ''
            || $formMchItem['send_type'] === null) {
            if (in_array('express', $sendType)) {
                $formMchItem['send_type'] = 'express';
            } elseif (in_array('offline', $sendType)) {
                $formMchItem['send_type'] = 'offline';
            } else {
                $formMchItem['send_type'] = 'city';
            }
        }
        if (!in_array($formMchItem['send_type'], $sendType)) {
            throw new OrderException('配送方式`' . $formMchItem['send_type'] . '`不正确。');
        }
        foreach ($sendType as $item) {
            $deliveryItem['delivery']['send_type']        = $formMchItem['send_type'];
            $deliveryItem['delivery']['disabled']         = false;
            $deliveryItem['delivery']['send_type_list'][] =
                [
                    'name'  => ($item == 'express') ? '快递配送' : ($item == 'offline' ? '上门自提' : '同城配送'),
                    'value' => $item,
                ];
        }
        return $deliveryItem;
    }

    /**
     * 门店
     * @param $mchItem
     * @param $formMchItem
     * @param $formData
     * @return array
     */
    protected function setStoreData($mchItem, $formMchItem, $formData)
    {
        $mchItem['store']               = null;
        $mchItem['store_select_enable'] = true;
        if ($mchItem['delivery']['send_type'] != 'offline') {
            return $mchItem;
        }
        $storeExists = Store::find()->where([
            'mall_id'   => \Yii::$app->mall->id,
            'mch_id'    => $mchItem['mch']['id'],
            'is_delete' => 0,
        ])->exists();
        if (!$storeExists) {
            $mchItem['no_store'] = true;
        }
        if ($mchItem['mch']['id'] != 0) {
            $mchItem['store_select_enable'] = false;
        }
        if (!empty($formMchItem['store_id'])) {
            $store = Store::find()
                ->where([
                    'id'        => $formMchItem['store_id'],
                    'mall_id'   => \Yii::$app->mall->id,
                    'mch_id'    => $mchItem['mch']['id'],
                    'is_delete' => 0,
                ])->asArray()->one();
        } else {
            $query = Store::find()
                ->where([
                    'mall_id'   => \Yii::$app->mall->id,
                    'mch_id'    => $mchItem['mch']['id'],
                    'is_delete' => 0,
                ]);
            if ($formMchItem['mch_id'] == 0) {
                $query->andWhere(['is_default' => 1]);
            }
            $store = $query->asArray()->one();
        }
        if (!$store) {
            return $mchItem;
        }
        if ($store['longitude']
            && $store['latitude']
            && !empty($formData['longitude'])
            && !empty($formData['latitude'])
            && is_numeric($formData['longitude'])
            && is_numeric($formData['latitude'])) {
            $store['distance'] = get_distance($store['longitude'], $store['latitude'], $formData['longitude'], $formData['latitude']);
        } else {
            $store['distance'] = '-m';
        }
        if (!empty($store['distance']) && is_numeric($store['distance'])) {
            // $store['distance'] 单位 m
            if ($store['distance'] > 1000) {
                $store['distance'] = number_format($store['distance'] / 1000, 2) . 'km';
            } else {
                $store['distance'] = number_format($store['distance'], 0) . 'm';
            }
        } else {
            $store['distance'] = '-m';
        }
        $mchItem['store'] = $store;
        return $mchItem;
    }

    /**
     * 包邮和运费
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param $expressItem
     * @return mixed
     * @throws OrderException
     */
    protected function setExpressData($expressItem)
    {
        $noZeroGoodsList              = []; // 没有被包邮的商品列表（未单独设置包邮规则）
        $noZeroIndRuleGoodsList       = []; // 没有被包邮的单独设置包邮规则的商品列表
        $zeroIndRuleGoodsList         = []; // 被包邮的单独设置包邮规则的商品列表
        $expressItem['express_price'] = price_format(0);

        if ($expressItem['delivery']['send_type'] == 'offline') { // 上门自提无需运费
            return $expressItem;
        }

        $address = $this->getUserAddress();
        if (!$address) {
            $expressItem['city']['error'] = '未选择收货地址';
            return $expressItem;
        }

        //以下代码没用
        if ($expressItem['delivery']['send_type'] == 'city') { // 同城配送
            if (!($address->longitude && $address->latitude)) {
                // 同城配送时，地址没有定位则将地址取消选中
                $this->userAddress = null;
                // 没有设置定位
                $expressItem['city']['error'] = '未选择收货地址';
                return $expressItem;
            }
            $point = [
                'lng' => $address->longitude,
                'lat' => $address->latitude
            ];
            $num   = 0;
            foreach ($expressItem['goods_list'] as $goodsItem) { // 按商品ID小计件数和金额，看是否达到包邮条件
                $num += $goodsItem['num'];
            }
            try {
                $commonDelivery = DeliveryCommon::getInstance();
                $cityConfig     = $commonDelivery->getConfig();
                if (isset($cityConfig['price_enable']) && $cityConfig['price_enable'] && $expressItem['total_goods_original_price'] < $cityConfig['price_enable']) {
                    $expressItem['city']['error'] = '未达到起送价' . $cityConfig['price_enable'] . '元';
                    return $expressItem;
                }
                $distance            = $commonDelivery->getDistance($point);
                $totalSecondPrice    = $commonDelivery->getPrice($distance, $num);
                $expressItem['city'] = [
                    'address' => $cityConfig['address']['address'],
                    'explain' => $cityConfig['explain']
                ];
            } catch (\Exception $exception) {
                $expressItem['city']['error'] = '用户定位地址不在配送范围内';
                return $expressItem;
            }
            $expressItem['distance']      = $distance;
            $expressItem['express_price'] = price_format($totalSecondPrice);
            $expressItem['total_price']   = price_format($expressItem['total_goods_price'] + $expressItem['express_price']);
            return $expressItem;
        }

        $groupGoodsTotalList = []; // 按商品id小计的商品金额和数量
        foreach ($expressItem['goods_list'] as $goodsItem) { // 按商品ID小计件数和金额，看是否达到包邮条件
            if (isset($groupGoodsTotalList[$goodsItem['id']])) {
                $groupGoodsTotalList[$goodsItem['id']]['total_price'] += $goodsItem['total_price'];
                $groupGoodsTotalList[$goodsItem['id']]['num']         += $goodsItem['num'];
            } else {
                $groupGoodsTotalList[$goodsItem['id']]['total_price'] = $goodsItem['total_price'];
                $groupGoodsTotalList[$goodsItem['id']]['num']         = $goodsItem['num'];
            }
            $groupGoodsTotalList[$goodsItem['id']]['total_price'] =
                price_format($groupGoodsTotalList[$goodsItem['id']]['total_price']);
        }
        foreach ($expressItem['goods_list'] as $goodsItem) {
            if (is_numeric($goodsItem['pieces']) && $goodsItem['pieces'] > 0) { // 单品设置了满件包邮
                if ($groupGoodsTotalList[$goodsItem['id']]['num'] >= $goodsItem['pieces']) { // 满足条件
                    $zeroIndRuleGoodsList[] = $goodsItem;
                } else { // 未满足条件
                    $noZeroIndRuleGoodsList[] = $goodsItem;
                }
            } elseif (is_numeric($goodsItem['forehead']) && $goodsItem['forehead'] > 0) { // 单品设置了满额包邮
                if ($groupGoodsTotalList[$goodsItem['id']]['total_price'] >= $goodsItem['forehead']) { // 满足条件
                    $zeroIndRuleGoodsList[] = $goodsItem;
                } else { // 未满足条件
                    $noZeroIndRuleGoodsList[] = $goodsItem;
                }
            } else { // 未设置包邮
                $noZeroGoodsList[] = $goodsItem;
            }
        }
        if (!count($noZeroGoodsList) && !count($noZeroIndRuleGoodsList)) {
            return $expressItem;
        }

        $globalZeroExpressPrice = false; // 是否全局包邮
        /** @var FreeDeliveryRules[] $freeDeliveries */
        $freeDeliveries = FreeDeliveryRules::find()->where([
            'is_delete' => 0,
            'mall_id'   => \Yii::$app->mall->id,
            'mch_id'    => $expressItem['mch']['id']
        ])->orderBy('price')->all();
        foreach ($freeDeliveries as $freeDelivery) {
            $districts  = $freeDelivery->decodeDetail();
            $inDistrict = false;
            foreach ($districts as $district) {
                if ($district['id'] == $address->province_id) {
                    $inDistrict = true;
                    break;
                } elseif ($district['id'] == $address->city_id) {
                    $inDistrict = true;
                    break;
                } elseif ($district['id'] == $address->district_id) {
                    $inDistrict = true;
                    break;
                }
            }
            if ($inDistrict && $expressItem['total_goods_original_price'] >= $freeDelivery->price) {
                $globalZeroExpressPrice = true;
                break;
            }
        }

        if ($globalZeroExpressPrice) { // 满足全局包邮规则 list = $noZeroIndRuleGoodsList
            $noZeroGoodsList = $noZeroIndRuleGoodsList;
        } else { // 未满足全局包邮规则 list = $noZeroGoodsList + $noZeroIndRuleGoodsList
            $noZeroGoodsList = array_merge($noZeroGoodsList, $noZeroIndRuleGoodsList);
        }

        $postageRuleGroups = []; // 商品按匹配到的运费规则进行分组
        $noPostageRuleHit  = true; // 没有比配到运费规则
        foreach ($noZeroGoodsList as $goodsItem) {
            if ($goodsItem['freight_id'] && $goodsItem['freight_id'] != -1) {
                $postageRule = PostageRules::findOne([
                    'mall_id'   => \Yii::$app->mall->id,
                    'id'        => $goodsItem['freight_id'],
                    'is_delete' => 0,
                    'mch_id'    => $expressItem['mch']['id'],
                ]);
                if (!$postageRule) {
                    $postageRule = PostageRules::findOne([
                        'mall_id'   => \Yii::$app->mall->id,
                        'status'    => 1,
                        'is_delete' => 0,
                        'mch_id'    => $expressItem['mch']['id'],
                    ]);
                }
            } else {
                $postageRule = PostageRules::findOne([
                    'mall_id'   => \Yii::$app->mall->id,
                    'status'    => 1,
                    'is_delete' => 0,
                    'mch_id'    => $expressItem['mch']['id'],
                ]);
            }
            if (!$postageRule) {
                continue;
            }

            $rule        = null; // 用户的收货地址是否在规则中
            $ruleDetails = $postageRule->decodeDetail();
            foreach ($ruleDetails as $ruleDetail) {
                foreach ($ruleDetail['list'] as $district) {
                    if ($district['id'] == $address->province_id) {
                        $rule = $ruleDetail;
                        break;
                    } elseif ($district['id'] == $address->city_id) {
                        $rule = $ruleDetail;
                        break;
                    } elseif ($district['id'] == $address->district_id) {
                        $rule = $ruleDetail;
                        break;
                    }
                }
                if ($rule) {
                    break;
                }
            }
            if (!$rule) {
                continue;
            }

            $noPostageRuleHit = false;
            if (!isset($postageRuleGroups['rule:' . $postageRule->id])) {
                $postageRuleGroups['rule:' . $postageRule->id] = [
                    'postage_rule' => $postageRule,
                    'rule'         => $rule,
                    'goods_list'   => [],
                ];
            }
            $postageRuleGroups['rule:' . $postageRule->id]['goods_list'][] = $goodsItem;
        }
        if ($noPostageRuleHit) {
            return $expressItem;
        }
        $firstPriceList   = [];
        $totalSecondPrice = 0;
        foreach ($postageRuleGroups as $group) {
            /** @var PostageRules $postageRule */
            $postageRule = $group['postage_rule'];
            $rule        = $group['rule'];
            $goodsList   = $group['goods_list'];
            $firstPrice  = $rule['firstPrice'];
            $secondPrice = 0;
            if ($postageRule->type == 1) { // 按重量计费
                $totalWeight = 0;
                foreach ($goodsList as $goods) {
                    if (is_nan($goods['goods_attr']['weight'])) {
                        throw new OrderException('商品`' . $goods['name'] . '的重量不是有效的数字。');
                    }
                    $totalWeight += ($goods['goods_attr']['weight'] * $goods['num']);
                }
                if ($rule['second'] > 0) {
                    $secondPrice = ceil(($totalWeight - $rule['first']) / $rule['second']) // 向上取整
                        * $rule['secondPrice'];
                } else {
                    $secondPrice = 0;
                }
            } elseif ($postageRule->type == 2) { // 按件数计费
                $totalNum = 0;
                foreach ($goodsList as $goods) {
                    $totalNum += $goods['num'];
                }
                if ($rule['second'] > 0) {
                    $secondPrice = ceil(($totalNum - $rule['first']) / $rule['second']) // 向上取整
                        * $rule['secondPrice'];
                } else {
                    $secondPrice = 0;
                }
            }
            if ($secondPrice < 0) {
                $secondPrice = 0;
            }
            $firstPriceList[] = $firstPrice;
            $totalSecondPrice += $secondPrice;
        }

        $expressItem['express_price'] = price_format(max($firstPriceList) + $totalSecondPrice);
        $expressItem['total_price']   = price_format($expressItem['total_goods_price'] + $expressItem['express_price']);
        return $expressItem;
    }

    protected function setOrderForm($mchItem)
    {
        $mchItem['order_form'] = null;
        if (!$this->enableOrderForm) {
            return $mchItem;
        }
        if ($mchItem['mch']['id'] != 0) {
            return $mchItem;
        }
        $option = OptionLogic::get(Option::NAME_ORDER_FORM, \Yii::$app->mall->id, Option::GROUP_APP);
        if (!$option) {
            return $mchItem;
        }
        if ($option['status'] != 1) {
            return $mchItem;
        }
        if (!empty($option['value']) && is_array($option['value'])) {
            foreach ($option['value'] as $k => $item) {
                $option['value'][$k]['is_required'] = $item['is_required'] == 1 ? 1 : 0;
            }
        }
        $mchItem['order_form'] = $option;
        return $mchItem;
    }

    protected function setGoodsForm($mchItem)
    {
        $defaultForm   = null;
        $noDefaultForm = false;
        $existsFormIds = [];
        $dataOfFormId  = [];
        $hasGoodsForm  = false;
        foreach ($mchItem['goods_list'] as &$goodsItem) {
            $goodsItem['form'] = null;
            if (!isset($goodsItem['form_id']) || $goodsItem['form_id'] == -1) {
                continue;
            }
            if ($goodsItem['form_id'] == 0) {
                if ($noDefaultForm) {
                    continue;
                }
                if (!$defaultForm) {
                    $defaultForm = Form::findOne([
                        'mall_id'    => \Yii::$app->mall->id,
                        'mch_id'     => $mchItem['mch']['id'],
                        'is_default' => 1,
                        'status'     => 1,
                        'is_delete'  => 0,
                    ]);
                    if (!$defaultForm) {
                        $noDefaultForm = true;
                        continue;
                    }
                }
                $form = $defaultForm;
            } else {
                $form = Form::findOne([
                    'id'        => $goodsItem['form_id'],
                    'mall_id'   => \Yii::$app->mall->id,
                    'mch_id'    => $mchItem['mch']['id'],
                    'status'    => 1,
                    'is_delete' => 0,
                ]);
            }
            if (!$form) {
                continue;
            }
            $hasGoodsForm = true;
            if (is_string($form->value)) {
                $form->value = \Yii::$app->serializer->decode($form->value);
            }
            if (is_array($form->value) || $form->value instanceof \ArrayObject) {
                foreach ($form->value as &$formItem) {
                    $formItem['is_required'] = $formItem['is_required'] == 1 ? 1 : 0;
                }
            }
            if (in_array($form->id, $existsFormIds)) {
                $sameForm = true;
            } else {
                $sameForm        = false;
                $existsFormIds[] = $form->id;
            }
            if (!$sameForm && !empty($goodsItem['form_data'])) {
                $dataOfFormId[$form->id] = $goodsItem['form_data'];
            } elseif ($sameForm && isset($dataOfFormId[$form->id])) {
                $goodsItem['form_data'] = $dataOfFormId[$form->id];
            }
            $goodsItem['form'] = [
                'id'        => $form->id,
                'name'      => $form->name,
                'value'     => $form->value,
                'same_form' => $sameForm,
            ];
        }
        $mchItem['diff_goods_form_count'] = intval(count($existsFormIds));
        $mchItem['has_goods_form']        = $hasGoodsForm;
        return $mchItem;
    }

    /**
     * 获取用户的收货地址
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @return UserAddress|null
     */
    protected function getUserAddress()
    {
        if ($this->userAddress) {
            return $this->userAddress;
        }
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        if (!isset($this->form_data['user_address_id']) || empty($this->form_data["user_address_id"])) {
            $userAddress = UserAddress::getOneData([
                'user_id'    => $user->id,
                'is_delete'  => 0,
                'is_default' => 1,
            ]);
            if (empty($userAddress)) {
                $userAddress = UserAddress::getUserAddressDefault([
                    'user_id'   => $user->id,
                    'is_delete' => 0,
                ]);
            }
            unset($userAddress["created_at"], $userAddress["updated_at"], $userAddress["deleted_at"], $userAddress["is_delete"]);
            $this->userAddress = $userAddress;
        } else {
            $this->userAddress = UserAddress::getOneData([
                'user_id'   => $user->id,
                'is_delete' => 0,
                'id'        => $this->form_data['user_address_id'],
            ]);
        }
        if (empty($this->userAddress)) {
            $this->userAddress = [];
        }
        return $this->userAddress;
    }

    /**
     * 获取可用的优惠券列表
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param $listData
     * @return array
     * @throws OrderException
     */
    public function getUsableCouponList($listData)
    {
        $list = $this->getListData([$listData]);
        if (!is_array($list) || !count($list)) {
            return [];
        }
        $data                    = $list[0];
        $goodsTotalOriginalPrice = 0;
        foreach ($data['goods_list'] as $goodsItem) {
            $goodsTotalOriginalPrice += $goodsItem['total_original_price'];
        }

        /** @var User $user */
        $user        = \Yii::$app->user->identity;
        $nowDateTime = time();

        /** @var UserCoupon[] $allList */
        $allList = UserCoupon::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id,],
            ['user_id' => $user->id],
            ['is_use' => 0],
            ['is_delete' => 0],
            [
                '<=',
                'begin_at',
                $nowDateTime
            ],
            [
                '>=',
                'end_at',
                $nowDateTime
            ],
            [
                '<=',
                'coupon_min_price',
                $goodsTotalOriginalPrice
            ],
        ])->with([
            'coupon' => function ($query) {
                /** @var Query $query */
            }
        ])->all();
        if (!count($allList)) {
            return [];
        }

        $goodsWarehouseIdList = [];
        $catIdList            = [];
        foreach ($data['goods_list'] as &$goodsItem) {
            $goods                          = Goods::findOne($goodsItem['id']);
            $goodsWarehouseIdList[]         = $goods->goods_warehouse_id;
            $goodsCatRelations              = GoodsCatRelation::findAll([
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'is_delete'          => 0,
            ]);
            $goodsItem['goodsCatRelations'] = $goodsCatRelations;
            $goodsItem['goods']             = $goods;
            foreach ($goodsCatRelations as $goodsCatRelation) {
                $catIdList[] = $goodsCatRelation->cat_id;
            }
        }

        $newList = [];
        foreach ($allList as &$userCoupon) {
            if (!$userCoupon->coupon) {
                continue;
            }
            $userCoupon->coupon_data               = \Yii::$app->serializer->decode($userCoupon->coupon_data);
            $userCoupon->coupon_data->appoint_type = $userCoupon->coupon->appoint_type;
            $userCoupon->coupon_data->name         = $userCoupon->coupon->name;

            if ($userCoupon->coupon->appoint_type == 2) {
                /** @var GoodsWarehouse[] $goodsList */
                $goodsWarehouseList = $userCoupon->coupon->goods;
                if (count($goodsWarehouseList)) {
                    $couponTotalGoodsPrice = 0;
                    foreach ($goodsWarehouseList as &$goodsWarehouse) {
                        foreach ($data['goods_list'] as &$goodsItem) {
                            $goods = $goodsItem['goods'];
                            if ($goods->goods_warehouse_id == $goodsWarehouse->id) {
                                $couponTotalGoodsPrice += $goodsItem['total_original_price'];
                            }
                        }
                        unset($goodsItem);
                    }
                    unset($goodsWarehouse);
                    foreach ($goodsWarehouseList as $goodsWarehouse) {
                        if (in_array($goodsWarehouse->id, $goodsWarehouseIdList) && $couponTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                            $newList[] = $userCoupon;
                            break;
                        }
                    }
                    continue;
                }
            } elseif ($userCoupon->coupon->appoint_type == 1) {
                $catList = $userCoupon->coupon->cat;
                if (count($catList)) {
                    $couponCatTotalGoodsPrice = 0;
                    foreach ($catList as &$cat) {
                        foreach ($data['goods_list'] as &$goodsItem) {
                            foreach ($goodsItem['goodsCatRelations'] as &$goodsCatRelation) {
                                if ($goodsCatRelation->cat_id == $cat->id) {
                                    $couponCatTotalGoodsPrice += $goodsItem['total_original_price'];
                                }
                            }
                        }
                        unset($goodsItem);
                    }
                    unset($cat);

                    foreach ($catList as $cat) {
                        if (in_array($cat->id, $catIdList) && $couponCatTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                            $newList[] = $userCoupon;
                            break;
                        }
                    }
                    continue;
                }
            } elseif ($userCoupon->coupon->appoint_type == 3) {
                $newList[] = $userCoupon;
            }
        }
        return $newList;
    }

    /**
     * 获取用户可用的优惠券列表
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param $listData
     * @return array
     * @throws OrderException
     */
    public function getUserUsableCoupon($coupon)
    {
        $couponItem['coupon'] = [
            'enabled'         => true,
            'use'             => false,
            'coupon_discount' => price_format(0),
            'user_coupon_id'  => 0,
            'coupon_error'    => "",
        ];
        if (!$this->enableCoupon || $couponItem['mch']['id'] != 0) { // 入住商不可使用优惠券
            $couponItem['coupon']['enabled'] = false;
            return $couponItem;
        }
        if (empty($formItem['user_coupon_id'])) {
            return $couponItem;
        }
        $nowDateTime = time();
        /** @var UserCoupon $userCoupon */
        $userCoupon = UserCoupon::getOneData([
            'AND',
            ['id' => $formItem['user_coupon_id']],
            ['user_id' => \Yii::$app->user->identity->getId()],
            ['is_delete' => 0],
            ['is_use' => 0],
            [
                '<=',
                'begin_at',
                $nowDateTime
            ],
            [
                '>=',
                'end_at',
                $nowDateTime
            ],
        ]);
        if (!$userCoupon) {
            $couponItem['coupon']['coupon_error'] = '优惠券不存在';
            return $couponItem;
        }
        /** @var Coupon $coupon */
        $coupon = Coupon::getOneData([
            'id' => $userCoupon->coupon_id,
        ]);
        if (!$coupon) {
            $couponItem['coupon']['coupon_error'] = '优惠券不存在';
            return $couponItem;
        }
        if ($coupon->appoint_type == Coupon::APPOINT_TYPE_CAT || $coupon->appoint_type == Coupon::APPOINT_TYPE_GOODS) {
            if ($coupon->type == Coupon::APPOINT_TYPE_CAT) { // 指定分类可用
                $couponCatRelations = CouponCatRelation::findAll([
                    'coupon_id' => $coupon->id,
                    'is_delete' => 0,
                ]);
                $catIdList          = [];
                foreach ($couponCatRelations as $couponCatRelation) {
                    $catIdList[] = $couponCatRelation->cat_id;
                }
                /** @var GoodsCatRelation[] $goodsCatRelations */
                $goodsCatRelations = GoodsCatRelation::find()
                    ->select('gcr.goods_warehouse_id')
                    ->alias('gcr')
                    ->leftJoin(['gc' => GoodsCats::tableName()], 'gcr.cat_id=gc.id')
                    ->where([
                        'gc.is_delete'  => 0,
                        'gcr.cat_id'    => $catIdList,
                        'gcr.is_delete' => 0
                    ])
                    ->all();
                $couponGoodsIdList = [];
                foreach ($goodsCatRelations as $goodsCatRelation) {
                    $couponGoodsIdList[] = $goodsCatRelation->goods_warehouse_id;
                }
            } else { // 指定商品可用
                $couponGoodsRelations = CouponGoodsRelation::findAll([
                    'coupon_id' => $coupon->id,
                    'is_delete' => 0,
                ]);
                $couponGoodsIdList    = [];
                foreach ($couponGoodsRelations as $couponGoodsRelation) {
                    $couponGoodsIdList[] = $couponGoodsRelation->goods_warehouse_id;
                }
            }
            $totalGoodsPrice         = 0;
            $totalGoodsOriginalPrice = 0;
            foreach ($couponItem['goods_list'] as $goodsItem) {
                if (!in_array($goodsItem['goods_warehouse_id'], $couponGoodsIdList)) {
                    continue;
                }
                $totalGoodsPrice         += $goodsItem['total_price'];
                $totalGoodsOriginalPrice += $goodsItem['total_original_price'];
            }
            if ($userCoupon->coupon_min_price > $totalGoodsOriginalPrice) { // 可用的商品原总价未达到优惠券使用条件
                $couponItem['coupon']['coupon_error'] = '所选优惠券未满足使用条件';
                return $couponItem;
            }
            $sub      = UserCouponLogic::getDiscountAmount($userCoupon, $totalGoodsOriginalPrice);
            $subPrice = min($totalGoodsPrice, $sub, $couponItem['total_goods_price']);
            if ($subPrice > 0) {
                $couponItem['total_goods_price']         = price_format($couponItem['total_goods_price'] - $subPrice);
                $couponItem['coupon']['use']             = true;
                $couponItem['coupon']['user_coupon_id']  = $userCoupon->id;
                $couponItem['coupon']['coupon_discount'] = price_format($subPrice);
            }
            $couponItem = $this->setDiscountPrice($couponItem, $totalGoodsPrice, $subPrice, $couponGoodsIdList);
        } elseif ($coupon->appoint_type == 3) { // 全商品通用
            if ($couponItem['total_goods_price'] <= 0) { // 价格已优惠到0不再使用优惠券
                $couponItem['coupon']['coupon_error'] = '商品价格已为0无法使用优惠券';
                return $couponItem;
            }
            if ($couponItem['total_goods_original_price'] < $userCoupon->coupon_min_price) { // 商品原总价未达到优惠券使用条件
                $couponItem['coupon']['coupon_error'] = '所选优惠券未满足使用条件';
                return $couponItem;
            }
            $subPrice = UserCouponLogic::getDiscountAmount($userCoupon, $couponItem['total_goods_original_price']);;
            if ($subPrice > $couponItem['total_goods_price']) {
                $subPrice = $couponItem['total_goods_price'];
            }
            $totalGoodsPrice                         = $couponItem['total_goods_price'];
            $couponItem['total_goods_price']         = price_format($couponItem['total_goods_price'] - $subPrice);
            $couponItem['coupon']['use']             = true;
            $couponItem['coupon']['user_coupon_id']  = $userCoupon->id;
            $couponItem['coupon']['coupon_discount'] = price_format($subPrice);
            $couponItem                              = $this->setDiscountPrice($couponItem, $totalGoodsPrice, $subPrice);
        }
        return $couponItem;

        $list = $this->getListData([$listData]);
        if (!is_array($list) || !count($list)) {
            return [];
        }
        $data                    = $list[0];
        $goodsTotalOriginalPrice = 0;
        foreach ($data['goods_list'] as $goodsItem) {
            $goodsTotalOriginalPrice += $goodsItem['total_original_price'];
        }

        /** @var User $user */
        $user        = \Yii::$app->user->identity;
        $nowDateTime = time();

        /** @var UserCoupon[] $allList */
        $allList = UserCoupon::find()->where([
            'AND',
            ['mall_id' => \Yii::$app->mall->id,],
            ['user_id' => $user->id],
            ['is_use' => 0],
            ['is_delete' => 0],
            [
                '<=',
                'begin_at',
                $nowDateTime
            ],
            [
                '>=',
                'end_at',
                $nowDateTime
            ],
            [
                '<=',
                'coupon_min_price',
                $goodsTotalOriginalPrice
            ],
        ])->with([
            'coupon' => function ($query) {
                /** @var Query $query */
            }
        ])->all();
        if (!count($allList)) {
            return [];
        }

        $goodsWarehouseIdList = [];
        $catIdList            = [];
        foreach ($data['goods_list'] as &$goodsItem) {
            $goods                          = Goods::findOne($goodsItem['id']);
            $goodsWarehouseIdList[]         = $goods->goods_warehouse_id;
            $goodsCatRelations              = GoodsCatRelation::findAll([
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'is_delete'          => 0,
            ]);
            $goodsItem['goodsCatRelations'] = $goodsCatRelations;
            $goodsItem['goods']             = $goods;
            foreach ($goodsCatRelations as $goodsCatRelation) {
                $catIdList[] = $goodsCatRelation->cat_id;
            }
        }

        $newList = [];
        foreach ($allList as &$userCoupon) {
            if (!$userCoupon->coupon) {
                continue;
            }
            $userCoupon->coupon_data               = \Yii::$app->serializer->decode($userCoupon->coupon_data);
            $userCoupon->coupon_data->appoint_type = $userCoupon->coupon->appoint_type;
            $userCoupon->coupon_data->name         = $userCoupon->coupon->name;

            if ($userCoupon->coupon->appoint_type == 2) {
                /** @var GoodsWarehouse[] $goodsList */
                $goodsWarehouseList = $userCoupon->coupon->goods;
                if (count($goodsWarehouseList)) {
                    $couponTotalGoodsPrice = 0;
                    foreach ($goodsWarehouseList as &$goodsWarehouse) {
                        foreach ($data['goods_list'] as &$goodsItem) {
                            $goods = $goodsItem['goods'];
                            if ($goods->goods_warehouse_id == $goodsWarehouse->id) {
                                $couponTotalGoodsPrice += $goodsItem['total_original_price'];
                            }
                        }
                        unset($goodsItem);
                    }
                    unset($goodsWarehouse);
                    foreach ($goodsWarehouseList as $goodsWarehouse) {
                        if (in_array($goodsWarehouse->id, $goodsWarehouseIdList) && $couponTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                            $newList[] = $userCoupon;
                            break;
                        }
                    }
                    continue;
                }
            } elseif ($userCoupon->coupon->appoint_type == 1) {
                $catList = $userCoupon->coupon->cat;
                if (count($catList)) {
                    $couponCatTotalGoodsPrice = 0;
                    foreach ($catList as &$cat) {
                        foreach ($data['goods_list'] as &$goodsItem) {
                            foreach ($goodsItem['goodsCatRelations'] as &$goodsCatRelation) {
                                if ($goodsCatRelation->cat_id == $cat->id) {
                                    $couponCatTotalGoodsPrice += $goodsItem['total_original_price'];
                                }
                            }
                        }
                        unset($goodsItem);
                    }
                    unset($cat);

                    foreach ($catList as $cat) {
                        if (in_array($cat->id, $catIdList) && $couponCatTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                            $newList[] = $userCoupon;
                            break;
                        }
                    }
                    continue;
                }
            } elseif ($userCoupon->coupon->appoint_type == 3) {
                $newList[] = $userCoupon;
            }
        }
        return $newList;
    }

    /**
     * 获取商品规格、用户库存操作
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param $goodsAttrId
     * @param Goods $goods
     * @return OrderGoodsAttr
     * @throws \Exception
     */
    public function getGoodsAttr($goodsAttrId, $goods)
    {
        $newGoodsAttr = $this->getGoodsAttrClass();
        $newGoodsAttr->setGoods($goods);
        $newGoodsAttr->setGoodsAttrById($goodsAttrId);

        return $newGoodsAttr;
    }

    /**
     * 商品规格类
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @return OrderGoodsAttr OrderGoodsAttr
     */
    public function getGoodsAttrClass()
    {
        return new OrderGoodsAttr();
    }

    /**
     * 商品库存操作
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param OrderGoodsAttr $goodsAttr
     * @param int $subNum
     * @param array $goodsItem
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function subGoodsNum($goodsAttr, $subNum, $goodsItem)
    {
        (new GoodsAttr())->updateStock($subNum, 'sub', $goodsAttr->id);
    }

    /**
     * 订单扩展
     * @param Order $order
     * @param $goodsItem
     * @param $commonOrderId
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function extraOrderDetail($order, $goodsItem, $commonOrderId = 0)
    {
        \Yii::warning('order_submit extraOrderDetail goodsItem=' . var_export($goodsItem, true));
        $orderDetail                        = new OrderDetail();
        $orderDetail->order_id              = $order->id;
        $orderDetail->goods_id              = $goodsItem['id'];
        $orderDetail->num                   = $goodsItem['num'];
        $orderDetail->unit_price            = $goodsItem['unit_price'];
        $orderDetail->total_original_price  = $goodsItem['total_original_price'];
        $orderDetail->total_price           = $goodsItem['total_price'];
        $orderDetail->member_discount_price = $goodsItem['member_discount'];
        $orderDetail->sign                  = $goodsItem['sign'];
        $orderDetail->goods_no              = $goodsItem['goods_attr']['no'] ?: '';
        $goodsInfo                          = [
            'attr_list'  => $goodsItem['attr_list'],
            'goods_attr' => $goodsItem['goods_attr'],
        ];
        $orderDetail->goods_info            = $orderDetail->encodeGoodsInfo($goodsInfo);
        $orderDetail->form_data             = \Yii::$app->serializer->encode(isset($goodsItem['form_data']) ? $goodsItem['form_data'] : null);
        $orderDetail->form_id               = (isset($goodsItem['form']) && isset($goodsItem['form']['id'])) ? $goodsItem['form']['id'] : 0;
        $orderDetail->use_user_coupon_id    = $goodsItem['coupon']['use'] ? $goodsItem['coupon']['user_coupon_id'] : 0;
        $orderDetail->coupon_discount_price = $goodsItem['coupon']['coupon_discount'];

        //规格商品积分使用
        $orderDetail->use_score_price = $goodsItem['use_score_price'];
        $orderDetail->use_score       = $goodsItem['use_score'];
        $orderDetail->score_price       = $goodsItem['score_price'];
        
        //购物券抵扣
        $orderDetail->integral_price       = $goodsItem['integral_price'];

        //满减金额
        $orderDetail->full_relief_price = $goodsItem['actual_full_relief_price'];

        $orderDetailId                      = $orderDetail->save();
        if (!$orderDetailId) {

            throw new \Exception((new BaseModel())->responseErrorMsg($orderDetail));
        }

//        $commonOrderForm = new CommonOrderForm();
//        $commonOrderForm->form_data["order_id"] = $order->id;
//        $commonOrderForm->form_data["order_detail_id"] = $orderDetail->id;
//        $commonOrderForm->form_data["order_no"] = $order->order_no;
//        $commonOrderForm->form_data["price"] = $goodsItem['total_price'];
//        $commonOrderForm->form_data["goods_type"] = CommonOrderDetail::GOODS_TYPE_MALL;
//        $result = $commonOrderForm->addCommonOrderDetail($commonOrderId,$goodsItem);
//        if (!$result) {
//            throw new \Exception("公共订单详情添加失败");
//        }

        // 优惠券标记已使用
        if ($orderDetail->use_user_coupon_id) {
            $userCoupon             = UserCoupon::findOne($orderDetail->use_user_coupon_id);
            $userCoupon->is_use     = 1;
            $userCoupon->is_failure = 1;
            if ($userCoupon->update(true, [
                    'is_use',
                    'is_failure'
                ]) === false) {
                throw new \Exception('优惠券状态更新失败。');
            }
        }
    }

    /**
     * 所选收货地址是否允许购买
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param Address $address
     * @param integer $mchItem
     * @return bool
     */
    protected function getUserAddressEnable($address, $mchItem)
    {
        $mchId = $mchItem['mch']['id'];
        if (!$address) {
            return true;
        }
        if (!$this->enableAddressEnable) {
            return true;
        }
        if (isset($mchItem['delivery'])
            && isset($mchItem['delivery']['send_type'])
            && $mchItem['delivery']['send_type'] == 'offline') {
            return true;
        }
        if (isset($mchItem['delivery'])
            && isset($mchItem['delivery']['send_type'])
            && $mchItem['delivery']['send_type'] == 'city') {
            return true;
        }

        $func = function ($area_limit, $address) {
            foreach ($area_limit as $group) {
                if (isset($group['list']) && is_array($group['list'])) {
                    foreach ($group['list'] as $item) {
                        if (isset($item['id'])) {
                            if ($item['id'] == $address->province_id
                                || $item['id'] == $address->city_id
                                || $item['id'] == $address->district_id) {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        };

        //商品自定义
        foreach ($mchItem['goods_list'] as $item) {
            $goods = $item['goods_attr']['goods'];
            if ($goods['is_area_limit'] === 1) {
                $area_limit = \yii\helpers\BaseJson::decode($goods{'area_limit'});
                if (!isset($area_limit) || !is_array($area_limit)) {
                    return false;
                }
                return $func($area_limit, $address);
            }
        }

        $model = OptionLogic::get(
            Option::NAME_TERRITORIAL_LIMITATION,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            ['is_enable' => 0],
            $mchId
        );
        if (!$model || !isset($model['is_enable'])) {
            return true;
        }
        if ($model['is_enable'] != 1) {
            return true;
        }

        if ((isset($model["detail"]) || $model["detail"] == null) && empty($model["detail"])) {
            return true;
        }
        if (!isset($model['detail']) || !is_array($model['detail'])) {
            return false;
        }
        return $func($model['detail'], $address);
    }

    /**
     * 是否达到起送规则
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param string $totalPrice
     * @param UserAddress $address
     * @param array $mchItem
     * @return bool
     */
    protected function getPriceEnable($totalPrice, $address, $mchItem)
    {
        if (!$this->enablePriceEnable) {
            return true;
        }
        $mchId = $mchItem['mch']['id'];
        if (isset($mchItem['delivery'])
            && isset($mchItem['delivery']['send_type'])
            && $mchItem['delivery']['send_type'] == 'city') {
            return true;
        }
        $model = OptionLogic::get(
            Option::NAME_OFFER_PRICE,
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            [
                'is_enable'   => 0,
                'total_price' => 0
            ],
            $mchId
        );
        if (!$model || !isset($model['is_enable'])) {
            return true;
        }
        if ($model['is_enable'] != 1) {
            return true;
        }
        $minPrice = null;
        if (is_array($model['detail'])) {
            foreach ($model['detail'] as $group) {
                $inArr = false;
                foreach ($group['list'] as $item) {
                    if (isset($item['id'])) {
                        if ($address && ($item['id'] == $address->province_id
                                || $item['id'] == $address->city_id
                                || $item['id'] == $address->district_id)) {
                            $inArr = true;
                            break;
                        }
                    }
                }
                if ($inArr) {
                    $minPrice = price_format($group['total_price']);
                    break;
                }
            }
        }
        if ($minPrice === null) {
            $minPrice = price_format($model['total_price']);
        }
        return $totalPrice >= $minPrice ? true : false;
    }

    /**
     * 商品信息的其他判断
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param Goods $goods
     * @param $item
     * @return bool
     * @throws OrderException
     */
    protected function checkGoods($goods, $item)
    {
        if ($goods->mch_id > 0) {
            $mch = Mch::findOne([
                'mall_id'       => \Yii::$app->mall->id,
                'is_delete'     => 0,
                'status'        => 1,
                'review_status' => 1,
                'id'            => $goods->mch_id
            ]);

            if (!$mch) {
                throw new OrderException('商户不存在或已关闭');
            }
        }
        return true;
    }

    /**
     * 额外的货币说明
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param Goods $goods
     * @param OrderGoodsAttr $goodsAttr
     * @return array 例如 ['2000积分']
     *
     */
    protected function getCustomCurrency($goods, $goodsAttr)
    {
        return [];
    }

    /**
     * 获取token
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @return string
     * @throws \yii\base\Exception
     */
    protected function getToken()
    {
        return \Yii::$app->security->generateRandomString();
    }

    /**
     * 额外的合计货币说明
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param array $listData
     * @return array 例如 ['2000积分']
     */
    protected function getCustomCurrencyAll($listData)
    {
        return [];
    }

    /**
     * 添加自定义额外的订单信息
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $order
     * @param $mchItem
     * @return bool
     */
    public function extraOrder($order, $mchItem)
    {
        return true;
    }

    /**
     * 设置总价格
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $totalPrice
     * @return mixed
     */
    protected function setTotalPrice($totalPrice)
    {
        return $totalPrice;
    }

    /**
     * 设置可优惠的价格
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $item
     * @param $totalGoodsPrice
     * @param $subPrice
     * @param array $couponGoodsIdList
     * @return mixed
     */
    private function setDiscountPrice(&$item, $totalGoodsPrice, $subPrice, $couponGoodsIdList = [])
    {
        if ($totalGoodsPrice <= 0) {
            return $item;
        }
        /* @var $resetPrice float 优惠券剩余可优惠金额 */
        $resetPrice = $subPrice;
        if ($couponGoodsIdList && !empty($couponGoodsIdList)
            && !in_array($item['goods_warehouse_id'], $couponGoodsIdList)) {
            return $item;
        }
        /* @var float $goodsPrice 商品可优惠的金额 */
        $goodsPrice = price_format($item['total_price'] * $subPrice / $totalGoodsPrice);
        if ($resetPrice < $goodsPrice && $resetPrice > 0) {
            $goodsPrice = $resetPrice;
        }
        $item['total_price'] -= min($goodsPrice, $item['total_price']);
        \Yii::warning("setDiscountPrice goodsPrice={$goodsPrice},subPrice={$resetPrice},totalGoodsPrice={$totalGoodsPrice},total_price=" . $item["total_price"]);
        return $item;
    }

    /**
     * 设置可优惠的价格(废弃)
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $item
     * @param $totalGoodsPrice
     * @param $subPrice
     * @param array $couponGoodsIdList
     * @return mixed
     */
    private function setDiscountPrice_($item, $totalGoodsPrice, $subPrice, $couponGoodsIdList = [])
    {
        if ($totalGoodsPrice <= 0) {
            return $item;
        }
        /* @var $resetPrice float 优惠券剩余可优惠金额 */
        $resetPrice = $subPrice;
        uasort($item['goods_list'], function ($a, $b) {
            if ($a['total_price'] == $b['total_price']) {
                return 0;
            }
            return ($a['total_price'] < $b['total_price']) ? -1 : 1;
        });
        $item['goods_list'] = array_values($item['goods_list']);
        foreach ($item['goods_list'] as $index => &$goods) {
            if ($couponGoodsIdList && !empty($couponGoodsIdList)
                && !in_array($goods['goods_warehouse_id'], $couponGoodsIdList)) {
                continue;
            }
            /* @var float $goodsPrice 商品可优惠的金额 */
            $goodsPrice = price_format($goods['total_price'] * $subPrice / $totalGoodsPrice);
            if ($resetPrice < $goodsPrice || ($index == count($item['goods_list']) - 1 && $resetPrice > 0)) {
                $goodsPrice = $resetPrice;
            }
            $resetPrice           -= $goodsPrice;
            $goods['total_price'] -= min($goodsPrice, $goods['total_price']);
        }
        unset($goods);
        return $item;
    }

    /**
     * 检查商品限单（商品可以下单的次数限制）
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $goodsList
     * @throws OrderException
     */
    public function checkGoodsOrderLimit($goodsList)
    {
        foreach ($goodsList as $goods) {
            if (!isset($goods['confine_order_count'])) {
                continue;
            }
            if ($goods['confine_order_count'] < 0) {
                continue;
            }
            if ($goods['confine_order_count'] == 0) {
                throw new OrderException('商品“' . $goods['name'] . '”已超出下单次数限制。');
            }
            $count = OrderDetail::find()->alias('od')
                ->select('od.order_id')
                ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                ->where([
                    'o.cancel_status' => 0,
                    'o.is_delete'     => 0,
                    'o.is_recycle'    => 0,
                    'o.user_id'       => \Yii::$app->user->id,
                    'od.is_delete'    => 0,
                    'od.goods_id'     => $goods['id'],
                ])
                ->groupBy('od.order_id')
                ->count();
            if ($count >= $goods['confine_order_count']) {
                throw new OrderException('商品“' . $goods['name'] . '”已超出下单次数限制。' . $count);
            }
        }
    }

    /**
     * 检查购买的商品数量是否超出限制及库存（购买数量含以往的订单）
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param array $goodsList [ ['id','name',''] ]
     * @throws OrderException
     */
    private function checkGoodsBuyLimit($goodsList)
    {
        $goodsIdMap = [];
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('商品' . $goods['name'] . '数量不能小于0');
            }
            if (isset($goodsIdMap[$goods['id']])) {
                $goodsIdMap[$goods['id']]['num'] += $goods['num'];
            } else {
                $goodsIdMap[$goods['id']]['num']   = $goods['num'];
                $goodsIdMap[$goods['id']]['goods'] = $goods['goods_attr']['goods'];
            }
        }
        foreach ($goodsIdMap as $goodsId => $item) {
            /** @var Goods $goods */
            $goods = $item['goods'];
            if ($goods->confine_count <= 0) {
                continue;
            }
            $oldOrderGoodsNum = OrderDetail::find()->alias('od')
                ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                ->where([
                    'od.goods_id'  => $goodsId,
                    'od.is_delete' => 0,
                    'o.user_id'    => \Yii::$app->user->id,
                    'o.is_delete'  => 0,
                ])
                ->andWhere([
                    '!=',
                    'o.cancel_status',
                    1
                ])
                ->sum('od.num');
            $oldOrderGoodsNum = $oldOrderGoodsNum ? intval($oldOrderGoodsNum) : 0;
            $totalNum         = $oldOrderGoodsNum + $item['num'];
            if ($totalNum > $goods->confine_count) {
                throw new OrderException('商品（' . $goods->goodsWarehouse->name . '）限购' . $goods->confine_count . '件');
            }
        }
    }

    /**
     * 检查商品库存是否充足
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param array $goodsList
     * @throws OrderException
     */
    public function checkGoodsStock($goodsList)
    {
        foreach ($goodsList as $goods) {
            if ($goods['num'] <= 0) {
                throw new OrderException('商品' . $goods['name'] . '数量不能小于0');
            }
            if (!empty($goods['goods_attr'])) {
                /** @var GoodsAttr $goodsAttr */
                $goodsAttr = $goods['goods_attr'];
                if ($goods['num'] > $goodsAttr->stock) {
                    throw new OrderException('商品' . $goods['name'] . '库存不足! ');
                }
            }
        }
    }

    /**
     * 发货方式兼容全平台之前的传入参数(暂废弃)
     */
    protected function changeParam()
    {
        if (version_compare(\Yii::$app->appVersion, '4.1.0', '<')) {
            foreach ($this->form_data['list'] as &$formMchList) {
                if (isset($formMchList['send_type'])) {
                    if ($formMchList['send_type'] === 1) {
                        $formMchList['send_type'] = 'express';
                    } elseif ($formMchList['send_type'] === 2) {
                        $formMchList['send_type'] = 'offline';
                    }
                }
            }
            unset($formMchList);
        }
    }

    /**
     * 发货方式兼容全平台之前的传出参数（暂废弃）
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @param array $data
     * @return array
     */
    protected function changeData($data)
    {
        $sendType = [
            'express' => 1,
            'offline' => 2
        ];
        if (version_compare(\Yii::$app->appVersion, '4.1.0', '<')) {
            foreach ($data['mch_list'] as &$formMchList) {
                $formMchList['delivery']['send_type'] = $sendType[$formMchList['delivery']['send_type']];
                foreach ($formMchList['delivery']['send_type_list'] as &$item) {
                    $item['value'] = $sendType[$item['value']];
                }
                unset($item);
            }
            unset($formMchList);
        }
        return $data;
    }

    /**
     * 获取配送方式
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $sendType
     * @return array
     */
    protected function getNewSendType($sendType)
    {
        $list = [];
        foreach ($sendType as $item) {
            $list[] = $item;
        }
        if (count($list) == 0) {
            $list[] = 'express';
        }
        return $list;
    }

    /**
     * 获取vip卡券优惠信息(暂废弃)
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $data
     * @return mixed
     */
    public function setVipDiscountData($data)
    {
        //权限判断
        $permission = \Yii::$app->branch->childPermission(\Yii::$app->mall->user->userInfo);
        if (!in_array('vip_card', $permission)) {
            return $data;
        }
        try {
            $plugin = \Yii::$app->plugin->getPlugin('vip_card');
            $data   = $plugin->vipDiscount($data);
            return $data;
        } catch (\Exception $e) {
            return $data;
        }
    }

    /**
     * 获取模板消息
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 16:33
     * @return array
     * @throws \app\core\exceptions\ClassNotFoundException
     */
    protected function getTemplateMessage()
    {
        $arr  = [
            'order_pay_tpl',
            'order_cancel_tpl',
            'order_send_tpl'
        ];
        $list = TemplateList::getInstance()->getTemplate(\Yii::$app->appPlatform, $arr);
        return $list;
    }


    /**
     * 预览订单加载可用的优惠券列表
     * @param $item
     * @return array
     */
    private function loadUserUsableCouponAllList($item)
    {
        $goodsTotalOriginalPrice = $item["total_goods_original_price"];

        /** @var User $user */
        $user        = \Yii::$app->user->identity;
        $nowDateTime = time();
        /** @var UserCoupon[] $allList */
        $allList = UserCoupon::getList([
            'mall_id'          => \Yii::$app->mall->id,
            'user_id'          => $user->id,
            'is_use'           => 0,
            'end_at'           => $nowDateTime,
            'is_failure'       => 0,
            'coupon_min_price' => $goodsTotalOriginalPrice,
        ]);
        if (!count($allList)) {
            return [];
        }

        $goodsWarehouseIdList = [];
        $catIdList            = [];
        foreach ($item['goods_list'] as &$goodsItem) {
            $goods                          = Goods::findOne($goodsItem['id']);
            $goodsWarehouseIdList[]         = $goods->goods_warehouse_id;
            $goodsCatRelations              = GoodsCatRelation::findAll([
                'goods_warehouse_id' => $goods->goods_warehouse_id,
                'is_delete'          => 0,
            ]);
            $goodsItem['goodsCatRelations'] = $goodsCatRelations;
            $goodsItem['goods']             = $goods;
            foreach ($goodsCatRelations as $goodsCatRelation) {
                $catIdList[] = $goodsCatRelation->cat_id;
            }
        }

        $newList = [];
        foreach ($allList as &$userCoupon) {
            if (!$userCoupon->coupon || empty($userCoupon->coupon_data)) {
                continue;
            }
            $coupon_data = \Yii::$app->serializer->decode($userCoupon->coupon_data);
            unset($coupon_data->created_at, $coupon_data->updated_at, $coupon_data->deleted_at, $coupon_data->is_delete, $coupon_data->cat->created_at,
                $coupon_data->cat->updated_at, $coupon_data->cat->deleted_at, $coupon_data->cat->is_delete);
            $coupon_data->begin_at                 = !empty($coupon_data->begin_at) ? date("Y-m-d", $coupon_data->begin_at) : 0;
            $coupon_data->end_at                   = !empty($coupon_data->end_at) ? date("Y-m-d", $coupon_data->end_at) : 0;
            $userCoupon->coupon_data               = $coupon_data;
            $userCoupon->coupon_data->appoint_type = $userCoupon->coupon->appoint_type;
            $userCoupon->coupon_data->name         = $userCoupon->coupon->name;

            if ($userCoupon->coupon->appoint_type == Coupon::APPOINT_TYPE_CAT || $userCoupon->coupon->appoint_type == Coupon::APPOINT_TYPE_GOODS) {
                if ($userCoupon->coupon->appoint_type == Coupon::APPOINT_TYPE_GOODS) {
                    /** @var GoodsWarehouse[] $goodsList */
                    $goodsWarehouseList = $userCoupon->coupon->goods;
                    if (count($goodsWarehouseList)) {
                        $couponTotalGoodsPrice = 0;
                        foreach ($goodsWarehouseList as &$goodsWarehouse) {
                            foreach ($item['goods_list'] as &$goodsItem) {
                                $goods = $goodsItem['goods'];
                                if ($goods->goods_warehouse_id == $goodsWarehouse->id) {
                                    $couponTotalGoodsPrice += $goodsItem['total_original_price'] * $goodsItem["num"];
                                }
                            }
                            unset($goodsItem);
                        }
                        unset($goodsWarehouse);
                        foreach ($goodsWarehouseList as $goodsWarehouse) {
                            if (in_array($goodsWarehouse->id, $goodsWarehouseIdList) && $couponTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                                $newList[] = $userCoupon;
                                break;
                            }
                        }
                        $newList["totalGoodsOriginalPrice"] = $couponTotalGoodsPrice;
                        continue;
                    }
                } elseif ($userCoupon->coupon->appoint_type == Coupon::APPOINT_TYPE_CAT) {
                    $catList = $userCoupon->coupon->cat;
                    if (count($catList)) {
                        $couponCatTotalGoodsPrice = 0;
                        foreach ($catList as &$cat) {
                            foreach ($item['goods_list'] as &$goodsItem) {
                                foreach ($goodsItem['goodsCatRelations'] as &$goodsCatRelation) {
                                    if ($goodsCatRelation->cat_id == $cat->id) {
                                        $couponCatTotalGoodsPrice += $goodsItem['total_original_price'] * $goodsItem["num"];
                                    }
                                }
                            }
                            unset($goodsItem);
                        }
                        unset($cat);

                        foreach ($catList as $cat) {
                            if (in_array($cat->id, $catIdList) && $couponCatTotalGoodsPrice >= $userCoupon->coupon_min_price) {
                                $newList[] = $userCoupon;
                                break;
                            }
                        }
                        $newList["totalGoodsOriginalPrice"] = $couponCatTotalGoodsPrice;
                        continue;
                    }
                }
            } else {
                $newList[] = $userCoupon;
            }
        }
        return $newList;
    }

    /**
     * 获取用户符合条件的优惠券
     * @Author: zal
     * @Date: 2020-05-05
     * @Time: 10:33
     * @param $couponItem
     * @param UserCoupon $userCoupon
     * @return mixed
     * @throws OrderException
     */
    private function getUserUsableCouponData($couponItem, $userCoupon)
    {
        $couponItem['coupon'] = [
            'enabled'         => true,
            'use'             => false,
            'coupon_discount' => price_format(0),
            'user_coupon_id'  => 0,
            'coupon_error'    => "",
        ];
        if (!$this->enableCoupon || $couponItem['mch']['id'] != 0) { // 入住商不可使用优惠券
            return false;
        }
        /** @var Coupon $coupon */
        $coupon = Coupon::getOneData([
            'id' => $userCoupon->coupon_id,
        ]);
        if (!$coupon) {
            return false;
        }
        if ($coupon->appoint_type == Coupon::APPOINT_TYPE_CAT || $coupon->appoint_type == Coupon::APPOINT_TYPE_GOODS) {
            if ($coupon->appoint_type == Coupon::APPOINT_TYPE_CAT) { // 指定分类可用
                $couponCatRelations = CouponCatRelation::findAll([
                    'coupon_id' => $coupon->id,
                    'is_delete' => 0,
                ]);
                $catIdList          = [];
                foreach ($couponCatRelations as $couponCatRelation) {
                    $catIdList[] = $couponCatRelation->cat_id;
                }
                /** @var GoodsCatRelation[] $goodsCatRelations */
                $goodsCatRelations = GoodsCatRelation::find()
                    ->select('gcr.goods_warehouse_id')
                    ->alias('gcr')
                    ->leftJoin(['gc' => GoodsCats::tableName()], 'gcr.cat_id=gc.id')
                    ->where([
                        'gc.is_delete'  => 0,
                        'gcr.cat_id'    => $catIdList,
                        'gcr.is_delete' => 0
                    ])
                    ->all();
                $couponGoodsIdList = [];
                foreach ($goodsCatRelations as $goodsCatRelation) {
                    $couponGoodsIdList[] = $goodsCatRelation->goods_warehouse_id;
                }
            } else { // 指定商品可用
                $couponGoodsRelations = CouponGoodsRelation::findAll([
                    'coupon_id' => $coupon->id,
                    'is_delete' => 0,
                ]);
                $couponGoodsIdList    = [];
                foreach ($couponGoodsRelations as $couponGoodsRelation) {
                    $couponGoodsIdList[] = $couponGoodsRelation->goods_warehouse_id;
                }
            }
            $totalGoodsPrice         = 0;
            $totalGoodsOriginalPrice = 0;
            foreach ($couponItem['goods_list'] as $goodsItem) {
                if (!in_array($goodsItem['goods_warehouse_id'], $couponGoodsIdList)) {
                    continue;
                }
                $totalGoodsPrice         += $goodsItem['total_price'];
                $totalGoodsOriginalPrice += $goodsItem['total_original_price'];
            }
            //可抵扣金额
            $sub      = UserCouponLogic::getDiscountAmount($userCoupon, $totalGoodsOriginalPrice);
            $subPrice = min($totalGoodsPrice, $sub, $couponItem['total_goods_price']);
            if ($subPrice > 0) {
                $couponItem['total_goods_price']         = price_format($couponItem['total_goods_price'] - $subPrice);
                $couponItem['coupon']['use']             = true;
                $couponItem['coupon']['user_coupon_id']  = $userCoupon->id;
                $couponItem['coupon']['coupon_discount'] = price_format($subPrice);
            }
            $couponItem = $this->setDiscountPrice($couponItem, $totalGoodsPrice, $subPrice, $couponGoodsIdList);
        } elseif ($coupon->appoint_type == 3) { // 全商品通用
            if ($couponItem['total_goods_price'] <= 0) { // 价格已优惠到0不再使用优惠券
                $couponItem['coupon']['coupon_error'] = '商品价格已为0无法使用优惠券';
                return $couponItem;
            }
            $subPrice = UserCouponLogic::getDiscountAmount($userCoupon, $couponItem['total_goods_original_price']);
            if ($subPrice > $couponItem['total_goods_price']) {
                $subPrice = $couponItem['total_goods_price'];
            }
            $totalGoodsPrice                         = $couponItem['total_goods_price'];
            $couponItem['total_goods_price']         = price_format($couponItem['total_goods_price'] - $subPrice);
            $couponItem['coupon']['use']             = true;
            $couponItem['coupon']['user_coupon_id']  = $userCoupon->id;
            $couponItem['coupon']['coupon_discount'] = price_format($subPrice);
            $couponItem                              = $this->setDiscountPrice($couponItem, $totalGoodsPrice, $subPrice);
        }
        return $couponItem;
    }

    /**
     * 下单可用优惠券列表
     * @param $item
     * @return array
     * @throws OrderException
     */
    protected function getUserUsableCouponToOrder($item)
    {
        $allList = $this->loadUserUsableCouponAllList($item);
        return $allList;
    }

    /**
     * 添加自定义额外的订单信息
     * @Author: zal
     * @Date: 2020-04-30
     * @Time: 14:33
     * @param $order
     * @param $orderItem
     * @return bool
     * @throws \Exception
     */
    public function extraCommonOrder($order, $orderItem)
    {
        $commonOrderFrom            = new CommonOrderForm();
        $formData                   = [];
        $formData["order_id"]       = $order->id;
        $formData["goods_list"]     = $orderItem['goods_list'];
        $formData["pay_price"]      = $order->total_pay_price;
        $commonOrderFrom->form_data = $formData;
        $result                     = $commonOrderFrom->addCommonOrder();
        return $result;
    }

    /**
     * 计算单品满额减免(多个商品)
     * @param $item
     */
    public function goodsFullRelief($item)
    {
        //累计减免金额
        $subFullReliefPrice = 0;
        //记录减免过的商品id
        $fullReliefGoodsIds = array();

        foreach ($item['goods_list'] as $key => $goodsItem) {
            $full_relief_price = isset($goodsItem['full_relief_price']) ? $goodsItem['full_relief_price'] : 0;
            $fulfil_price      = isset($goodsItem['fulfil_price']) ? $goodsItem['fulfil_price'] : 0;

            $result = $this->goodsFullReliefOne($goodsItem['id'], $goodsItem['unit_price'], $goodsItem['total_original_price'], $goodsItem['total_price'], $goodsItem['num'], $fulfil_price, $full_relief_price);

            if (!$result) {
                continue;
            }

            //单个商品总价
            $item['goods_list'][$key]['total_price'] = $result['total_price'];
            //商品单价
            $item['goods_list'][$key]['change_unit_price'] = $result['change_unit_price'];
            //单个商品总价满额减免
            $item['goods_list'][$key]['full_relief_price'] = $result['full_relief_price'];
            //单个商品满额减免价
            $item['goods_list'][$key]['per_full_relief_price'] = $result['per_full_relief_price'];

            //记录减免的商品id
            array_push($fullReliefGoodsIds, $result['goods_id']);
            //记录所有商品总减免金额
            $subFullReliefPrice += $result['full_relief_price'];
        }
        //所有商品减免后的金额
        //$item['total_goods_price'] = $item['total_goods_price'] - $subFullReliefPrice;
        $item['total_full_relief_price'] = $subFullReliefPrice;
        return $item;
    }

    /**
     * 计算单品满额减免(单个商品)
     * @param $goods_id //商品id
     * @param $total_original_price //商品原价
     * @param $fulfil_price //满额
     * @param $full_relief_price //满额减免
     * @return array|false
     */
    public function goodsFullReliefOne($goods_id, $change_unit_price, $total_original_price, $total_price, $num, $fulfil_price, $full_relief_price)
    {
        if ($total_original_price <= 0) {
            return false;
        }

        $full_relief_price = min($total_original_price, $full_relief_price);

        if ($full_relief_price > 0 && $total_original_price >= $fulfil_price) {

            $total_price = $total_price - $full_relief_price;

            $per_full_relief_price = price_format($full_relief_price / $num);

            $change_unit_price = price_format($change_unit_price - $per_full_relief_price);

            return [
                'goods_id'              => $goods_id,
                'total_price'           => $total_price,
                'full_relief_price'     => $full_relief_price,
                'per_full_relief_price' => $per_full_relief_price,
                'change_unit_price'     => $change_unit_price
            ];
        }

        return false;
    }
}
