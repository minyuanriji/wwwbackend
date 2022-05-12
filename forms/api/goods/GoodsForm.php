<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-28
 * Time: 11:52
 */

namespace app\forms\api\goods;

use app\core\ApiCode;
use app\events\StatisticsEvent;
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
use app\models\GoodsWarehouse;
use app\models\Mall;
use app\models\Option;
use app\models\StatisticsBrowseLog;
use app\models\User;
use app\models\UserCoupon;
use app\plugins\mch\models\Mch;
use app\services\Goods\PriceDisplayService;
use Exception;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class GoodsForm extends BaseModel
{
    public $id;
    public $mall;
    public $user;
    public $goods;
    public $attr;
    public $isExpress = false;

    public function rules()
    {
        return [
            [['id'], 'integer'],
        ];
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 10:34
     * @Note:商品详情
     * @return array
     */
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            $goods = Goods::findOne(['is_delete' => 0, 'is_recycle' => 0, 'id' => $this->id, 'status' => 1, 'mall_id' => \Yii::$app->mall->id]);
            if (!$goods) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '该商品不存在或者已下架！');
            }
            //增加足迹

            if (!\Yii::$app->user->isGuest) {
                $footmark = GoodsFootmark::findOne(['user_id' => \Yii::$app->user->id, 'goods_id' => $this->id]);
                if (!$footmark) {
                    $footmark = new GoodsFootmark();
                    $footmark->goods_id = $this->id;
                    $footmark->user_id = \Yii::$app->user->id;
                    $footmark->mall_id = $goods->mall_id;
                }
                $footmark->is_delete = 0;
                $footmark->save();
            }
            $this->goods = $goods;
            $wareHouse = $goods->goodsWarehouse;
            $goods_collect = GoodsCollect::findOne(['goods_id' => $this->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id]);
            $info['collect']['is_collect'] = 0;
            $info['collect']['collect_id'] = 0;
            if ($goods_collect) {
                $info['collect']['is_collect'] = 1;
                $info['collect']['collect_id'] = $goods_collect->id;
            }
            $info['name'] = $wareHouse->name;
            $info['product'] = $wareHouse->product;
            $info['original_price'] = $wareHouse->original_price;
            $info['cover_pic'] = $wareHouse->cover_pic;
            $info['video_url'] = $wareHouse->video_url;
            $info['detail'] = $wareHouse->detail;
            $info['unit'] = $goods->unit;
            $info['sales'] = $goods->sales;
            $info['is_show_sales'] = $goods->is_show_sales;
            $info['use_attr'] = $goods->use_attr;
            $info['goods_stock'] = $goods->goods_stock;

            $info['order_prompt'] = $goods->order_prompt;
            $info['order_prompt_content'] = $goods->order_prompt_content;

            $info['max_deduct_integral'] = $goods->max_deduct_integral;

            //可抵金豆券大于0才显示金豆券会员价
            $PriceDisplayService=new PriceDisplayService(\Yii::$app->mall->id);
            if ($goods->max_deduct_integral > 0) {
                $info['price_display'] = $PriceDisplayService->getGoodsPriceDisplay($goods->price_display);
            } else {
                $info['price_display'] = [];
            }

            $info['app_share_pic'] = $goods->app_share_pic;
            $info['app_share_title'] = $goods->app_share_title;

            if ($goods->use_virtual_sales) {
                $info['sales'] = $info['sales'] + $this->goods->virtual_sales;
            }
            $info['pic_list'] = SerializeHelper::decode($wareHouse->pic_url);
            if ($goods->use_attr) {
                $attr_groups = $goods->attr_groups;
                $info['attr_groups'] = SerializeHelper::decode($attr_groups);
            }
            $this->setAttr($goods->attr);
            $info['attr_list'] = $this->attr;
            $info['service_list'] = $this->getServices();
            $info['max_price'] = $this->getPriceMax();
            $info['min_price'] = $this->getPriceMin();
            $coupon_list1 = Coupon::find()
                ->alias('c')
                ->andWhere(['c.appoint_type' => 3])
                ->andWhere(['!=', 'c.total_count', 0])
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => \Yii::$app->mall->id])
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
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => \Yii::$app->mall->id])
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
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => \Yii::$app->mall->id])
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
                          if(!\Yii::$app->user->isGuest){
                              $user_coupon = UserCoupon::findOne(['user_id' => \Yii::$app->user->identity->id, 'is_use' => 0, 'is_delete' => 0, 'coupon_id' => $coupon['id']]);
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

            $info['goods_activities'] = $this->getGoodsActivities();
            $info['coupon_list'] = $newCouponList;

            //商家
            $mchModel = $goods->mch_id ? $goods->mch : null;
            $mchInfo = [];
            if($mchModel && $mchModel->store){
                $mchInfo = $mchModel->store->getAttributes();
            }

            //商品是否是爆品
            

            \Yii::$app->trigger(StatisticsBrowseLog::EVEN_STATISTICS_LOG, new StatisticsEvent(['mall_id'=>\Yii::$app->mall->id,'browse_type'=>2,'user_id'=>\Yii::$app->user->id,'user_ip'=>$_SERVER['REMOTE_ADDR']]) );
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['goods' => $info, 'is_mch' => !empty($mchInfo) ? 1 : 0, 'mch' => $mchInfo]);
        } catch (\Exception $e) {
            \Yii::error($e);

            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), $this->errors);
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-28
     * @Time: 15:33
     * @Note:设置规格
     * @param null $attr
     * @throws Exception
     */
    public function setAttr($attr = null)
    {
        if (!$this->goods) {
            throw new \Exception('请先设置商品对象');
        }
        if (!$attr) {
            $attr = $this->goods->attr;
        }
        $newAttr = [];
        $attrGroup = \Yii::$app->serializer->decode($this->goods->attr_groups);
        $attrList = $this->goods->resetAttr($attrGroup);
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 10:17
     * @Note:商品最低价
     * @return float|string
     */
    public function getPriceMin()
    {
        $attr = $this->attr;
        $price = $this->goods->price;
        foreach ($attr as $index => $item) {
            $price = min($price, $item['price']);
        }
        return price_format($price, 'float', 2);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 10:18
     * @Note: 获取商品最高价
     * @return float|string
     */
    public function getPriceMax()
    {
        $attr = $this->attr;
        $price = $this->goods->price;
        foreach ($attr as $index => $item) {
            $price = max($price, $item['price']);
        }
        return price_format($price, 'float', 2);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 10:17
     * @Note:获取商品服务
     * @return array
     */
    public function getServices()
    {
        $services = [];
        if ($this->goods->is_default_services == 1) {
            $defaultService = GoodsService::find()->where([
                'is_default' => 1,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
            ])->orderBy('sort asc')->all();
        } else {
            $defaultService = $this->goods->services;
        }
        /* @var $defaultService GoodsService[] */
        foreach ($defaultService as $item) {
            $services[] = $item->name;
        }
        return $services;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-05
     * @Time: 14:16
     * @Note:获取商品活动
     * @return array
     */
    public function getGoodsActivities()
    {
        $options = OptionLogic::getList(
            [
                Option::NAME_TERRITORIAL_LIMITATION,
                Option::NAME_OFFER_PRICE,
            ],
            \Yii::$app->mall->id,
            Option::GROUP_APP,
            ['is_enable' => 0]
        );

        $limit = '';
        $detail = $this->getAreaLimit();
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
        if ($this->goods->pieces > 0) {
            $shipping .= sprintf('单品满%s件包邮，', $this->goods->pieces);
            if ($this->goods->pieces == 1) {
                $this->isExpress = true;
            }
        }
        if ($this->goods->forehead > 0) {
            $shipping .= sprintf('单品满￥%s包邮，', $this->goods->forehead);
            if ($this->goods->forehead < $this->getPriceMin()) {
                $this->isExpress = true;
            }
        }
        if (!$shipping) {
            $freeDelivery = FreeDeliveryRules::findAll([
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => 0,
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
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-30
     * @Time: 10:18
     * @Note:计算允许购买的区域
     * @return array|mixed
     */
    public function getAreaLimit()
    {
        $list = [];
        if ($this->goods->is_area_limit == 1) {
            $list = json_decode($this->goods->area_limit, true);
        } else {
            $territorial = OptionLogic::get(
                Option::NAME_TERRITORIAL_LIMITATION,
                \Yii::$app->mall->id,
                Option::GROUP_APP,
                ['is_enable' => 0],
                $this->goods->mch_id
            );
            if ($territorial && isset($territorial['is_enable']) && $territorial['is_enable'] == 1
                && isset($territorial['detail']) && is_array($territorial['detail'])) {
                $list = $territorial['detail'];
            }
        }
        return $list;
    }
}
