<?php
namespace app\forms\api\goods;

use app\canal\table\User;
use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\helpers\ArrayHelper;
use app\helpers\SerializeHelper;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\FreeDeliveryRules;
use app\models\Goods;
use app\models\GoodsAttr;
use app\models\GoodsCatRelation;
use app\models\GoodsCollect;
use app\models\GoodsFootmark;
use app\models\GoodsService;
use app\models\Option;
use app\models\UserCoupon;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;
use app\plugins\seckill\models\SeckillGoodsPrice;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetGoods;
use app\services\Goods\PriceDisplayService;

class CacheGoodsDetailForm extends BaseModel implements ICacheForm{

    public $id;
    public $user_id;
    public $mall_id;

    private $isExpress;
    private $attr;

    public function rules(){
        return [
            [['id', 'user_id'], 'integer'],
        ];
    }

    public function getSourceDataForm(){

        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {
            $goods = Goods::findOne([
                'is_delete'  => 0,
                'is_recycle' => 0,
                'id'         => $this->id,
                'status'     => 1,
//                'mall_id'    => $this->mall_id
            ]);
            if (!$goods) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '该商品不存在或者已下架！');
            }
            //增加足迹
            if ($this->user_id) {
                $footmark = GoodsFootmark::findOne(['user_id' => $this->user_id, 'goods_id' => $this->id]);
                if (!$footmark) {
                    $footmark = new GoodsFootmark();
                    $footmark->goods_id = $this->id;
                    $footmark->user_id = $this->user_id;
                    $footmark->mall_id = $goods->mall_id;
                }
                $footmark->is_delete = 0;
                $footmark->save();
            }

            $wareHouse = $goods->goodsWarehouse;
            $goods_collect = GoodsCollect::findOne(['goods_id' => $this->id, 'is_delete' => 0, 'user_id' => $this->user_id]);
            $info['collect']['is_collect'] = 0;
            $info['collect']['collect_id'] = 0;
            if ($goods_collect) {
                $info['collect']['is_collect'] = 1;
                $info['collect']['collect_id'] = $goods_collect->id;
            }
            $info['name'] = $wareHouse->name;
            $info['original_price'] = $wareHouse->original_price;
            $info['cover_pic'] = $wareHouse->cover_pic;
            $info['video_url'] = $wareHouse->video_url;
            $info['detail'] = $wareHouse->detail;
            $info['unit'] = $goods->unit;
            $info['sales'] = $goods->sales;
            $info['is_show_sales'] = $goods->is_show_sales;
            $info['use_attr'] = $goods->use_attr;
            $info['goods_stock'] = $goods->goods_stock;

            $info['max_deduct_integral'] = $goods->max_deduct_integral;

            //可抵红包券大于0才显示红包券会员价
            $PriceDisplayService=new PriceDisplayService($this->mall_id);
            if ($goods->max_deduct_integral > 0) {
                $info['price_display'] = $PriceDisplayService->getGoodsPriceDisplay($goods->price_display);
            } else {
                $info['price_display'] = [];
            }

            $info['app_share_pic'] = $goods->app_share_pic;
            $info['app_share_title'] = $goods->app_share_title;

            if ($goods->use_virtual_sales) {
                $info['sales'] = $info['sales'] + $goods->virtual_sales;
            }
            $info['pic_list'] = SerializeHelper::decode($wareHouse->pic_url);
            if ($goods->use_attr) {
                $attr_groups = $goods->attr_groups;
                $info['attr_groups'] = SerializeHelper::decode($attr_groups);
            }

            $this->setAttr($goods, $goods->attr);

            $info['attr_list'] = $this->attr;
            $info['service_list'] = $this->getServices($goods);
            $info['max_price'] = $this->getPriceMax($goods);
            $info['min_price'] = $this->getPriceMin($goods);
            $coupon_list1 = Coupon::find()
                ->alias('c')
                ->andWhere(['c.appoint_type' => 3])
                ->andWhere(['!=', 'c.total_count', 0])
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => $this->mall_id])
                ->asArray()
                ->all();
            $goodsWarehouse = $goods->goodsWarehouse;

            //分类
            $coupon_list2 = Coupon::find()
                ->alias('c')
                ->leftJoin(['ccr' => CouponCatRelation::tableName()], 'ccr.coupon_id=c.id')
                ->leftJoin(['gcr' => GoodsCatRelation::tableName()], 'gcr.cat_id=ccr.cat_id')
                ->andWhere(['c.appoint_type' => 1])
                ->andWhere(['!=', 'c.total_count', 0])
                ->andWhere(['ccr.is_delete' => 0])
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => $this->mall_id])
                ->andWhere(['gcr.goods_warehouse_id' => $goodsWarehouse->id])
                ->asArray()
                ->all();
            //商品
            $coupon_list3 = Coupon::find()
                ->alias('c')
                ->leftJoin(['cgr' => CouponGoodsRelation::tableName()], 'cgr.coupon_id=c.id')
                ->andWhere(['c.appoint_type' => 2])
                ->andWhere(['!=', 'c.total_count', 0])
                ->andWhere(['cgr.is_delete' => 0])
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => $this->mall_id])
                ->andWhere(['cgr.goods_warehouse_id' => $goodsWarehouse->id])
                ->asArray()
                ->all();
            $coupon_list = array_merge($coupon_list1, $coupon_list2, $coupon_list3);
            $newCouponIds=[];
            $newCouponList=[];
            if (count($coupon_list)) {
                foreach ($coupon_list as $coupon) {
                    if(!in_array($coupon['id'],$newCouponIds)){
                        array_push($newCouponIds,$coupon['id']);
                        $coupon['is_receive'] = 0;
                        $coupon['begin_at'] = date('Y-m-d', $coupon['begin_at']);
                        $coupon['end_at'] = date('Y-m-d', $coupon['end_at']);
                        if($this->user_id){
                            $user_coupon = UserCoupon::findOne(['user_id' => $this->user_id, 'is_use' => 0, 'is_delete' => 0, 'coupon_id' => $coupon['id']]);
                            if ($user_coupon) {
                                $coupon['is_receive'] = 1;
                            }
                        }
                        $newCouponList[]=$coupon;
                    }
                }
                unset($coupon);
                unset($newCouponIds);
                unset($coupon_list);
            }

            $info['goods_activities'] = $this->getGoodsActivities($goods);
            $info['coupon_list']      = $newCouponList;

            //商家
            $mchModel = $goods->mch_id ? $goods->mch : null;
            $mchInfo = [];
            if($mchModel && $mchModel->store){
                $mchInfo = $mchModel->store->getAttributes();
            }

            //商品是否是爆品

            //判断是否可以购买
            $is_buy_power = 0;
            if ($this->user_id) {
                $user = \app\models\User::findOne($this->user_id);
                if ($goods->purchase_permission) {
                    $purchase_permission = json_decode($goods->purchase_permission,true);
                    if($user && !$user->is_delete){
                        if (in_array($user->role_type, $purchase_permission)) {
                            $is_buy_power = 1;
                        }
                    }
                } else {
                    $is_buy_power = 1;
                }
            }

            //判断是否是购物券商品
            $info['shopping_voucher'] = [
                "is_shopping_voucher_goods" => 0,
                'user_shopping_voucher'     => 0,
                'voucher_price'             => 0
            ];
            $voucherGoods = ShoppingVoucherTargetGoods::findOne([
                "is_delete" => 0,
                "goods_id"  => $this->id
            ]);
            if($voucherGoods){
                $info['shopping_voucher']['is_shopping_voucher_goods'] = 1;
                $info['shopping_voucher']['voucher_price'] = $voucherGoods->voucher_price;
                if($this->user_id){
                    $user = \app\models\User::findOne($this->user_id);
                    $info['shopping_voucher']['user_shopping_voucher'] = $user ? $user->shoppingVoucherUser->money : 0;
                }
            }

            $info['is_seckill'] = 0;// 0、否  1、积分秒杀商品
            $info['seckill_goods'] = [];
            $info['surplus_time'] = 0;
            //判断商品是否是秒杀商品
            $seckillGoodsResult = SeckillGoods::find()->andWhere(['goods_id' => $this->id, 'is_delete' => 0])->asArray()->all();
            if ($seckillGoodsResult) {
                $backSeckillGoodsResult = array_combine(array_column($seckillGoodsResult, 'seckill_id'), $seckillGoodsResult);
                $seckill_ids = array_column($seckillGoodsResult, 'seckill_id');
                $seckillResult = Seckill::find()->andWhere(['and',
                    ['in', 'id', $seckill_ids],
                    ['<', 'start_time', time()],
                    ['>', 'end_time', time()]
                ])->asArray()->one();
                $compare = [];
                if ($seckillResult && isset($backSeckillGoodsResult[$seckillResult['id']])) {
                    $seckillResult['goods_id'] = $backSeckillGoodsResult[$seckillResult['id']]['goods_id'];
                    $seckillResult['buy_limit'] = $backSeckillGoodsResult[$seckillResult['id']]['buy_limit'];
                    $seckillResult['virtual_seckill_num'] = $backSeckillGoodsResult[$seckillResult['id']]['virtual_seckill_num'];
                    $seckillResult['real_stock'] = $backSeckillGoodsResult[$seckillResult['id']]['real_stock'];
                    $seckillResult['virtual_stock'] = $backSeckillGoodsResult[$seckillResult['id']]['virtual_stock'];
                    $seckillResult['seckill_goods_id'] = $backSeckillGoodsResult[$seckillResult['id']]['id'];

                    //获取秒杀商品规格价格
                    $seckillResult['seckill_goods_price'] = SeckillGoodsPrice::find()->where(['seckill_goods_id' => $seckillResult['seckill_goods_id']])->asArray()->all();
                    if ($seckillResult['seckill_goods_price'] && isset($info['attr_list'])) {

                        //获取最小价格
                        $seckillMinPrice = array_column($seckillResult['seckill_goods_price'], 'seckill_price');
                        $info['min_price'] = min($seckillMinPrice);
                        $info['max_price'] = max($seckillMinPrice);

                        //修改商品规格价格
                        $seckillResult['seckill_goods_price'] = array_combine(array_column($seckillResult['seckill_goods_price'], 'attr_id'), $seckillResult['seckill_goods_price']);
                        foreach ($info['attr_list'] as &$item) {
                            if (isset($seckillResult['seckill_goods_price'][$item['id']])) {
                                if ($seckillResult['seckill_goods_price'][$item['id']]['shopping_voucher_deduction_price'] > 0) {
                                    $item['price'] = $seckillResult['seckill_goods_price'][$item['id']]['shopping_voucher_deduction_price'];
                                } else {
                                    $item['price'] = $seckillResult['seckill_goods_price'][$item['id']]['score_deduction_price'];
                                }
                                $item['operating_expenses_price'] = $seckillResult['seckill_goods_price'][$item['id']]['seckill_price'];
                                $compare[] = $item['price'];
                            } else {
                                $item['operating_expenses_price'] = $item['price'];
                            }
                        }
                    }

                    $info['is_seckill'] = 1;
                    $seckillResult['seckill_goods_price'] = array_values($seckillResult['seckill_goods_price']);
                    $info['seckill_goods'] = $seckillResult;
                    $info['surplus_time'] = $seckillResult['end_time'];
                    if($voucherGoods) {
                        $info['shopping_voucher']['voucher_price'] = $compare ? min($compare) : 0;
                    }
                }
            }

            $sourceData = $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '',
                [
                    'goods' => $info,
                    'is_mch' => !empty($mchInfo) ? 1 : 0,
                    'mch' => $mchInfo,
                    'is_buy_power' => $is_buy_power,
                ]);

            return new APICacheDataForm([
                "sourceData" => $sourceData
            ]);
        } catch (\Exception $e) {
            \Yii::error($e);
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), $this->errors);
        }
    }

    public function getCacheKey(){
        $keys[] = (int)$this->id;
        $keys[] = (int)$this->mall_id;
        $keys[] = (int)$this->user_id;
        return $keys;
    }

    public function setAttr(Goods $goods, $attr = null){
        if (!$goods) {
            throw new \Exception('请先设置商品对象');
        }
        if (!$attr) {
            $attr = $goods->attr;
        }
        $newAttr = [];
        $attrGroup = \Yii::$app->serializer->decode($goods->attr_groups);
        $attrList = $goods->resetAttr($attrGroup);
        /* @var GoodsAttr[] $attr */
        foreach ($attr as $key => $item) {
            // print_r($item);exit();
            $newItem = ArrayHelper::toArray($item);
            $newItem['attr_list'] = $attrList[$item['sign_id']];
            $newAttr[] = $newItem;
        }
        $this->attr = $newAttr;
    }

    /**
     * 获取商品活动
     * @return array
     */
    private function getGoodsActivities(Goods $goods){
        $options = OptionLogic::getList(
            [
                Option::NAME_TERRITORIAL_LIMITATION,
                Option::NAME_OFFER_PRICE,
            ],
            $this->mall_id,
            Option::GROUP_APP,
            ['is_enable' => 0]
        );

        $limit = '';
        $detail = $this->getAreaLimit($goods);
        if ($detail && !empty($detail)) {
            $detail = implode('、', array_column($detail[0]['list'], 'name'));
            $limit = sprintf('仅限%s购买', $detail);
        }
        //起送
        $pickup = '';
        $offer = $options[Option::NAME_OFFER_PRICE];
        if ($offer{'is_enable'} == 1) {
            if (is_array($offer['detail'])) {
                foreach ($offer['detail'] as $i) {
                    $pickup .= sprintf('满%s元起送', $i['total_price']);
                    $pickup .= '(';
                    $pickup .= implode('、', array_column($i['list'], 'name'));
                    $pickup .= ')，';
                }
            }
            $isTotalPrice = isset($offer['is_total_price']) ? $offer['is_total_price'] : 1;
            if ($isTotalPrice) {
                $pickup .= sprintf('满%s元起送(其他省份)', $offer['total_price']);
            }
            $pickup = substr($pickup, -1) == ',' ? substr($pickup, 0, -1) : $pickup;
        }
        //包邮
        $shipping = '';
        if ($goods->pieces > 0) {
            $shipping .= sprintf('单品满%s件包邮，', $goods->pieces);
            if ($goods->pieces == 1) {
                $this->isExpress = true;
            }
        }
        if ($goods->forehead > 0) {
            $shipping .= sprintf('单品满￥%s包邮，', $goods->forehead);
            if ($goods->forehead < $this->getPriceMin($goods)) {
                $this->isExpress = true;
            }
        }
        if (!$shipping) {
            $freeDelivery = FreeDeliveryRules::findAll([
                'is_delete' => 0,
                'mall_id'   => $this->mall_id,
                'mch_id'    => 0,
            ]);
            foreach ($freeDelivery as $i) {
                $shipping .= $i['price'] > 0 ? sprintf('满%s元包邮', $i['price']) : '免运费';
                $shipping .= '(';
                $shipping .= implode('、', array_column(\yii\helpers\Json::decode($i['detail']), 'name'));
                $shipping .= ')，';
            }
        }
        $shipping = trim($shipping, '\，');
        return [
            'limit' => $limit,
            'pickup' => $pickup,
            'shipping' => $shipping
        ];
    }

    /**
     * 计算允许购买的区域
     * @param Goods $goods
     * @return array|mixed
     */
    public function getAreaLimit(Goods $goods){
        $list = [];
        if ($goods->is_area_limit == 1) {
            $list = json_decode($goods->area_limit, true);
        } else {
            $territorial = OptionLogic::get(
                Option::NAME_TERRITORIAL_LIMITATION,
                $this->mall_id,
                Option::GROUP_APP,
                ['is_enable' => 0],
                $goods->mch_id
            );
            if ($territorial && isset($territorial['is_enable']) && $territorial['is_enable'] == 1
                && isset($territorial['detail']) && is_array($territorial['detail'])) {
                $list = $territorial['detail'];
            }
        }
        return $list;
    }

    /**
     * 获取商品最高价
     * @param Goods $goods
     * @return float|string
     */
    public function getPriceMax(Goods $goods)
    {
        $attr = $this->attr;
        $price = $goods->price;
        foreach ($attr as $index => $item) {
            $price = max($price, $item['price']);
        }
        return price_format($price, 'float', 2);
    }

    /**
     * 商品最低价
     * @param Goods $goods
     * @return float|string
     */
    public function getPriceMin(Goods $goods)
    {
        $attr = $this->attr;
        $price = $goods->price;
        foreach ($attr as $index => $item) {
            $price = min($price, $item['price']);
        }
        return price_format($price, 'float', 2);
    }

    /**
     * 获取商品服务
     * @param Goods $goods
     * @return array
     */
    public function getServices(Goods $goods)
    {
        $services = [];
        if ($goods->is_default_services == 1) {
            $defaultService = GoodsService::find()->where([
                'is_default' => 1,
                'is_delete' => 0,
                'mall_id' => $this->mall_id,
            ])->orderBy('sort asc')->all();
        } else {
            $defaultService = $goods->services;
        }
        /* @var $defaultService GoodsService[] */
        foreach ($defaultService as $item) {
            $services[] = $item->name;
        }
        return $services;
    }
}