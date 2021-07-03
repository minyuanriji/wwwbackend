<?php

namespace app\plugins\group_buy\forms\api;

use app\events\OrderEvent;
use app\forms\api\order\CommonOrderForm;
use app\models\BaseModel;
use app\models\CommonOrderDetail;
use app\plugins\group_buy\forms\mall\MultiActiveEditForm;
use app\plugins\group_buy\jobs\OrderSubmitJob;
use app\forms\api\order\OrderGoodsAttr;
use app\forms\api\order\OrderSubmitForm as ParentOrderSubmitForm;
use app\core\ApiCode;
use app\core\exceptions\OrderException;
use app\forms\common\order\OrderCommon;
use app\logic\CommonLogic;
use app\logic\CouponLogic;
use app\logic\OptionLogic;
use app\models\Goods;
use app\models\Mall;
use app\models\Option;
use app\plugins\group_buy\models\Order;
use app\models\UserAddress;
use app\models\UserCoupon;
use app\plugins\group_buy\models\PluginGroupBuyGoods;
use app\services\Order\AttrGoodsService;
use app\services\Order\CouponService;
use app\services\Order\FullReliefPriceService;
use app\services\Order\SameGoodsService;
use app\services\Order\ScoreService;
use app\validators\PhoneNumberValidator;
use app\plugins\group_buy\forms\mall\GroupBuyGoodsAttrQueryForm;
use yii\helpers\ArrayHelper;
use app\plugins\group_buy\forms\common\ActiveItemEditForm;
use app\plugins\group_buy\forms\common\ActiveQueryCommonForm;

class OrderSubmitForm extends ParentOrderSubmitForm
{
    /** @var UserAddress */
    protected $userAddress;

    protected $sign = "group_buy";

    protected $supportPayTypes;

    /**
     * 是否开启会员价会员折扣功能
     * @var bool
     */
    protected $enableMemberPrice = false;

    /**
     * 是否开启优惠券功能
     * @var bool
     */
    protected $enableCoupon = true;

    /**
     * 是否开启积分功能
     * @var bool
     */
    protected $enableScore = false;

    /**
     * 是否开启自定义表单功能
     * @var bool
     */
    protected $enableOrderForm = false;

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

        $checkGroupBuy = $this->checkGroupBuy($this->form_data);

        if ($checkGroupBuy['code'] > 0) {
            return $checkGroupBuy;
        }

        try {

            $data = $this->handleData();

        } catch (OrderException $orderException) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, CommonLogic::getExceptionMessage($orderException));
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $data);
    }

    /**
     * 拼团价
     * @param $goodsAttrId
     * @param \app\models\Goods $goods
     * @return \app\forms\api\order\OrderGoodsAttr
     * @throws \Exception
     */
    public function getGoodsAttr($goodsAttrId, $goods)
    {
        $origin = parent::getGoodsAttr($goodsAttrId, $goods);

        $origin['price'] = GroupBuyGoodsAttrQueryForm::getGroupBuyPriceByAttrId($goodsAttrId);

        if ($origin['price'] == 0) {
            throw new OrderException("拼团价格为0");
        }

        return $origin;
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
        $goods = $this->getGoodsOne($goodsItem['id']);

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
            'full_relief_price'    => $goods->full_relief_price
        ];
        return $itemData;
    }

    protected function getGoodsOne($goods_id)
    {
        return Goods::find()->with('goodsWarehouse')->where([
            'id'        => $goods_id,
            'mall_id'   => \Yii::$app->mall->id,
            'status'    => 1,
            'is_delete' => 0,
            'is_recycle' => 0,
        ])->one();
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

    public function checkGroupBuy($form_data)
    {
        //参团
        if (!empty($form_data['active_id'])) {
            $ActiveQueryCommonForm         = new ActiveQueryCommonForm();
            $ActiveQueryCommonForm->id     = $form_data['active_id'];
            $ActiveQueryCommonForm->status = 1;
            $active                        = $ActiveQueryCommonForm->returnOne();

            if (!$active) {
                return $this->returnApiResultData(103, "拼团不存在或已过期");
            }

            $ActiveItemEditForm = new ActiveItemEditForm();
            $check_repeat       = $ActiveItemEditForm->checkRepeatBuyingSql($form_data['active_id'], \Yii::$app->user->id);

            if ($check_repeat) {
                return $this->returnApiResultData(102, "当前用户已经参与过该拼团");
            }
        } else {
            if (!isset($form_data['list'][0]['goods_list'][0]['id'])) {
                return $this->returnApiResultData(105, "商品id不能为空");
            }

            $goods_id = $form_data['list'][0]['goods_list'][0]['id'];

            $group_buy_goods = PluginGroupBuyGoods::find()->where(['goods_id' => $goods_id, 'status' => 1, 'deleted_at' => 0])->one();

            if (!$group_buy_goods) {
                return $this->returnApiResultData(104, "拼团商品不存在或已过期");
            }

            if ($group_buy_goods->goods_stock < $group_buy_goods->people) {
                return $this->returnApiResultData(103, "商品库存不足，无法开团");
            }
        }

        return $this->returnApiResultData(0, "检查通过");
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


        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        $checkGroupBuy = $this->checkGroupBuy($this->form_data);

        if ($checkGroupBuy['code'] > 0) {
            return $checkGroupBuy;
        }

        try {
            $data = $this->handleData($type = 2);

        } catch (OrderException $orderException) {
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

//        $token   = $this->getToken();
//        $dataArr = [
//            'mall'                 => \Yii::$app->mall,
//            'user'                 => \Yii::$app->user->identity,
//            'form_data'            => $this->form_data,
//            'token'                => $token,
//            'sign'                 => $this->sign,
//            'return_data'          => $data,
//            'supportPayTypes'      => $this->supportPayTypes,
//            'enableMemberPrice'    => $this->enableMemberPrice,
//            'enableCoupon'         => $this->enableCoupon,
//            'enableScore'          => $this->enableScore,
//            'enableOrderForm'      => $this->enableOrderForm,
//            'enablePriceEnable'    => $this->enablePriceEnable,
//            'enableAddressEnable'  => $this->enableAddressEnable,
//            'OrderSubmitFormClass' => static::class,
//            'status'               => $this->status,
//            'appVersion'           => \Yii::$app->appVersion,
//        ];
//
//        $class = new OrderSubmitJob($dataArr);
//
//        $queueId = \Yii::$app->queue->delay(0)->push($class);


        //下订单新逻辑
        $t = \Yii::$app->db->beginTransaction(); //事务开始
        try {
            $token = $this->getToken();

            $oldOrder = Order::findOne(['token' => $token, 'sign' => $this->sign, 'is_delete' => 0]);
            if ($oldOrder) return $this->returnApiResultData(ApiCode::CODE_FAIL, '重复下单。');
            if (!$data['user_address_enable']) return $this->returnApiResultData(ApiCode::CODE_FAIL, '当前收货地址不允许购买。');
            if (!$data['price_enable']) return $this->returnApiResultData(ApiCode::CODE_FAIL, '订单总价未达到起送要求。');
            $user = \Yii::$app->user->identity;

            $event_data = array();//事件参数

            foreach ($data['list'] as $orderItem) {
                $order           = new Order();
                $order->mall_id  = \Yii::$app->mall->id;
                $order->user_id  = $user->getId();
                $order->mch_id   = $orderItem['mch']['id'];
                $order->order_no = Order::getOrderNo('S');;
                $order->total_price                = $orderItem['total_price'];
                $order->total_pay_price            = $orderItem['total_price'];
                $order->express_original_price     = $orderItem['express_price'];
                $order->express_price              = $orderItem['express_price'];
                $order->total_goods_price          = $orderItem['total_goods_price'];
                $order->total_goods_original_price = $orderItem['total_goods_original_price'];
                $order->member_discount_price      = $orderItem['member_discount'];
                $order->use_user_coupon_id         = 0;
                $order->coupon_discount_price      = 0;
                $order->use_score                  = $orderItem['score']['use'] ? $orderItem['score']['use_num'] : 0;
                $order->score_deduction_price      = $orderItem['score']['use'] ? $orderItem['score']['deduction_price'] : 0;
                $order->name                       = $data['user_address']['name'];
                $order->mobile                     = $data['user_address']['mobile'];
                if ($orderItem['delivery']['send_type'] !== 'offline') {
                    $order->address = $data['user_address']['province']
                        . ' '
                        . $data['user_address']['city']
                        . ' '
                        . $data['user_address']['district']
                        . ' '
                        . $data['user_address']['detail'];

                    $order->address_id = $data['user_address']['id'];
                }
                $order->remark     = empty($orderItem['remark']) ? "" : $orderItem['remark'];
                $order->order_form = $order->encodeOrderForm($orderItem['order_form_data']);
                $order->distance   = isset($orderItem['form_data']['distance']) ? $orderItem['form_data']['distance'] : 0;//同城距离
                $order->words      = '';

                $order->is_pay            = Order::IS_PAY_NO;
                $order->pay_type          = Order::IS_PAY_NO;
                $order->is_send           = 0;
                $order->is_confirm        = Order::IS_COMMENT_NO;
                $order->is_sale           = 0;
                $order->support_pay_types = $order->encodeSupportPayTypes($this->supportPayTypes);
                if ($orderItem['delivery']['send_type'] === 'offline') {
                    if (empty($orderItem['store'])) return $this->returnApiResultData(ApiCode::CODE_FAIL, '请选择自提门店。');
                    $order->store_id  = $orderItem['store']['id'];
                    $order->send_type = Order::SEND_TYPE_SELF;
                } elseif ($orderItem['delivery']['send_type'] === 'city') {
                    $order->distance  = $orderItem['distance'];
                    $order->location  = $data['user_address']['longitude'] . ',' . $data['user_address']['latitude'];
                    $order->send_type = Order::SEND_TYPE_CITY;
                    $order->store_id  = 0;
                } else {
                    $order->send_type = Order::SEND_TYPE_EXPRESS;
                    $order->store_id  = 0;
                }
                $order->sign   = $this->sign !== null ? $this->sign : '';
                $order->token  = $token;
                $order->status = $this->status;
                if (!$order->save()) {
                    return $this->returnApiResultData(ApiCode::CODE_FAIL, (new BaseModel())->responseErrorMsg($order));
                }

                foreach ($orderItem['goods_list'] as $goodsItem) {
                    $this->subGoodsNum($goodsItem['goods_attr'], $goodsItem['num'], $goodsItem);
                    $this->extraOrderDetail($order, $goodsItem);
                }

                // 优惠券标记已使用(此段代码没有用)
                if ($order->use_user_coupon_id) {
                    $userCoupon             = UserCoupon::findOne($order->use_user_coupon_id);
                    $userCoupon->is_use     = 1;
                    $userCoupon->is_failure = 1;
                    if ($userCoupon->update(true, ['is_use']) === false) {
                        return $this->returnApiResultData(ApiCode::CODE_FAIL, '优惠券状态更新失败。');
                    }
                }
                // 扣除积分
                if ($order->use_score) {
                    if (!\Yii::$app->currency->setUser($user)->score->sub($order->use_score, '下单积分抵扣')) {
                        return $this->returnApiResultData(ApiCode::CODE_FAIL, '积分操作失败。');
                    }
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
                //事件参数
                $event             = new OrderEvent();
                $event->order      = $order;
                $event->sender     = $this;
                $event->cartIds    = $cartIds;
                $event->formData   = [];
                $event->pluginData = ['sign' => 'vip_card', 'vip_discount' => $orderItem['vip_discount'] ?? null];
                $event->orderItem  = $orderItem;

                array_push($event_data,$event); //保存事件参数,待入库后触发
                //添加公共订单任务
                $commonOrderForm = new CommonOrderForm();
                // 不走队列
                // $commonOrderForm->commonOrderJob($order->id, CommonOrderDetail::STATUS_NORMAL, CommonOrderDetail::TYPE_MALL_GOODS, $order->mall_id, $order->user_id, $order->total_pay_price);
                $commonOrderForm->createCommonOrder($order->id, CommonOrderDetail::STATUS_NORMAL, $this->sign, $order->mall_id, $order->user_id, $order->total_pay_price);

            }

            $t->commit();

            //遍历触发事件
            foreach ($event_data as $event) {
                \Yii::$app->trigger(Order::EVENT_CREATED, $event);
                \Yii::$app->trigger(Order::EVENT_GROUP_BUY_CREATED, $event);
                $this->saveGroupBuy($event);
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", [
                'token'    => $token,
                'queue_id' => 0
            ]);

        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

        //下订单新逻辑
    }

    /**
     * 生成拼团
     * @param $event
     * @throws \Exception
     */
    public function saveGroupBuy($event)
    {
        $event = ArrayHelper::toArray($event);

        $form                                   = new MultiActiveEditForm();
        $data                                   = [];
        $data['active']['goods_id']             = $event['sender']['form_data']['list'][0]['goods_list'][0]['id'];
        $data['active_item']['attr_id']         = $event['sender']['form_data']['list'][0]['goods_list'][0]['goods_attr_id'];
        $data['active_item']['user_id']         = $event['order']['user_id'];
        $data['active_item']['group_buy_price'] = $event['order']['total_goods_price'];
        $data['active_item']['order_id']        = $event['order']['id'];
        if (isset($event['sender']['form_data']['active_id'])) {
            $data['active']['id'] = $event['sender']['form_data']['active_id'];
        }

        $form->attributes = $data;
        $return           = $form->save();

        if (!isset($return['code'])) {
            \Yii::warning("拼团订单生成失败:" . json_encode($return));
            throw new \Exception('拼团订单生成失败'. json_encode($return));
        }

        if ($return['code'] > 0) {
            \Yii::warning("拼团订单生成失败2:" . json_encode($return));
            throw new \Exception($return['msg'],$return['code']);
        }

        \Yii::info("拼团订单生成成功:" . json_encode($return));
    }

    /**
     * 处理数据
     * @param $type 1预览订单2提交订单
     * @return array
     * @throws OrderException
     * @throws \app\core\exceptions\ClassNotFoundException
     * @throws \yii\db\Exception
     */
    public function handleData($type = 1)
    {
        if (!OrderCommon::checkIsBindMobile()) {
            return $this->returnApiResultData(ApiCode::CODE_BIND_MOBILE, '请先绑定手机');
        }

        $listData = $this->getListData($this->form_data["list"]);

        foreach ($listData as &$item) {
            $goods_list = $item['goods_list'];
            $this->checkGoodsStock($goods_list);

            $formDataItem            = $item['form_data'];
            $item['express_price']   = price_format(0);
            $item['remark']          = isset($formDataItem['remark'])
                ? $formDataItem['remark'] : null;
            $item['order_form_data'] = isset($formDataItem['order_form'])
                ? $formDataItem['order_form'] : null;
            $totalGoodsPrice         = 0;
            $totalGoodsOriginalPrice = 0;

            foreach ($goods_list as $i => $goodsItem) {
                $totalGoodsPrice         += $goodsItem['total_price'];
                $totalGoodsOriginalPrice += $goodsItem['total_original_price'];
            }

            unset($goodsItem);
            $item['goods_list']                 = $goods_list;
            $item['total_goods_price']          = price_format($totalGoodsPrice);
            $item['total_goods_original_price'] = price_format($totalGoodsOriginalPrice);

            //找出多规格同一商品，区分规格商品列表合并成同一商品列表
            $SameGoodsService = new SameGoodsService($item);
            $item             = $SameGoodsService->getSameGoods();

            //优惠券
            $CouponService = new CouponService($item, $type, $this->enableCoupon);
            $item          = $CouponService->getUserGoodsCouponList();
            $CouponService->setFormData($item['form_data']);
            $item = $CouponService->getUsableUserCouponId();
            //优惠卷结束

            //会员价减免
            $item = $this->setMemberDiscountData($item);

            //满额减免
            $FullReliefPriceService = new FullReliefPriceService($item);
            $item                   = $FullReliefPriceService->countFullRelief();

            //优惠卷计算
            $item = $this->setCouponDiscountData($item, $formDataItem, $type);

            //是否使用积分减免
            if (isset($this->form_data['use_score']) && $this->form_data['use_score'] == 1) {
                $use_score = true;
            } else {
                $use_score = false;
            }

            //计算积分
            $ScoreService = new ScoreService($item, $type, $use_score, $this->enableScore);
            $item         = $ScoreService->countScore();

            //same_goods_list转换到goods_list入库
            $AttrGoodsService = new AttrGoodsService($item);
            $item             = $AttrGoodsService->getGoodsList();
            //
            $item['same_goods_list'] = $SameGoodsService->toArray($item['same_goods_list']);

            $item['total_price'] = $item['total_goods_price'];

            $item = $this->setDeliveryData($item, $formDataItem);

            $item = $this->setExpressData($item);

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

        return [
            'list'                => $listData,
            'total_price'         => price_format($total_price),
            'user_coupon'         => $userCouponList,
            'price_enable'        => $priceEnable,
            'user_address'        => $hasCity ? (($userAddress && $userAddress->longitude && $userAddress->latitude) ? $userAddress : []) : $userAddress,
            'user_address_enable' => $addressEnable,
            'is_self_mention'     => $isSelfMention,
            'custom_currency_all' => $this->getcustomCurrencyAll($listData),
            'all_self_mention'    => $allSelfMention,
            'hasCity'             => $hasCity,
            'score_enable'        => $score_enable,
        ];
    }
}