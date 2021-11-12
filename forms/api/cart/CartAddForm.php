<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 购物车api-添加购物车
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\cart;

use app\core\ApiCode;
use app\events\CartEvent;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Cart;
use app\models\GoodsAttr;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class CartAddForm extends BaseModel
{
    public $goods_id;
    public $attr;
    public $num;
    public $mch_baopin_id;
    public $buy_now;

    public function rules()
    {
        return [
            [['goods_id', 'attr', 'num'], 'required'],
            [['goods_id', 'num', 'attr', 'mch_baopin_id', 'buy_now'], 'integer'],
        ];
    }

    /**
     * 加入购物车
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @return array
     */
    public function addCart()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {
            $goods = Goods::findOne($this->goods_id);
            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $attr = GoodsAttr::find()->alias('g')->where([
                'g.id'        => $this->attr,
                'g.goods_id'  => $this->goods_id,
                'g.is_delete' => 0,
            ])->innerJoinwith(['goods o' => function ($query) {
                $query->where([
                    'o.id'        => $this->goods_id,
                    'o.mall_id'   => \Yii::$app->mall->id,
                    'o.is_delete' => 0,
                    'o.status'    => Goods::STATUS_ON,
                ]);
            }])->one();

            //判断是否是秒杀商品并且在秒杀活动内
            $check = $this->checkBuyPower($this->goods_id);

            if ($check && isset($check['seckillGoodsPrice'])) {
                $backSeckillGoodsPrice = array_combine(array_column($check['seckillGoodsPrice'], 'attr_id'), $check['seckillGoodsPrice']);
                if (isset($backSeckillGoodsPrice[$this->attr])) {
                    $attr->price = $backSeckillGoodsPrice[$this->attr]['score_deduction_price'];
                }
            }
            if (!$attr) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"商品异常");
            }

            $this->num = $this->num > $attr->stock ? $attr->stock : $this->num;
            if ($this->num <= 0) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL,"数量为空或库存为空");
            }

            $cart = Cart::findOne([
                'user_id' => \Yii::$app->user->id,
                'goods_id' => $this->goods_id,
                'attr_id' => $this->attr,
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
            ]);

            if (empty($cart)) {
                $cart = new Cart();
                $cart->mall_id   = \Yii::$app->mall->id;
                $cart->user_id   = \Yii::$app->user->id;
                $cart->goods_id  = $this->goods_id;
                $cart->attr_id   = $this->attr;
                $cart->num       = 0;
                $cart->mch_id    = $goods->mch_id;
            }

            $cart->attr_info = \Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
            $cart->num       = $this->num;
            $cart->buy_now   = (int)$this->buy_now ?? 0;
            $cart->mch_baopin_id = $this->mch_baopin_id;
            if ($cart->save()) {
                \Yii::$app->trigger(Cart::EVENT_CART_ADD, new CartEvent(['cartIds' => [$cart->id]]));
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"加入购物车成功", [
                    "cart_id" => $cart->id,
                    'cart_num' => Cart::find()->where(['buy_now' => 0, 'user_id' => \Yii::$app->user->id, 'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->count()
                ]);
            } else {
                return $this->returnApiResultData(999,"",$cart);
            }
        } catch (\Exception $e) {
            //Cart::cacheStatusSet(false);
            return $this->returnApiResultData(ApiCode::CODE_FAIL, CommonLogic::getExceptionMessage($e));
        }
    }


    /*购买前检测
     * @throws Exception
     * */
    private function checkBuyPower ($goods_id)
    {
        //查询该商品是否是秒杀商品及活动时间, 秒杀商品是否还有库存
       $seckillGoodsResult = SeckillGoods::judgeSeckillGoods($goods_id);
       if ($seckillGoodsResult) {
           if ($seckillGoodsResult['buy_limit'] > 0) {
               if ($this->num > $seckillGoodsResult['buy_limit']) {
                   throw new Exception('每人最多限购'. $seckillGoodsResult['buy_limit'] .'单', '', 1);
               }

               $buyNum = SeckillGoods::SeckillGoodsBuyNum($goods_id, $seckillGoodsResult);
               if ($buyNum + $this->num > $seckillGoodsResult['real_stock']) {
                   $surplus = $seckillGoodsResult['real_stock'] - $buyNum;
                   throw new Exception('秒杀商品库存不足，还剩余'. (($surplus < 0) ? 0 : $surplus) . '件', '', 1);
               }

               $userBuyNum = SeckillGoods::SeckillGoodsBuyNum($goods_id, $seckillGoodsResult, \Yii::$app->user->id);
               if ($userBuyNum + $this->num > $seckillGoodsResult['buy_limit']) {
                   throw new Exception('每人最多限购'. $seckillGoodsResult['buy_limit'] .'单', '', 1);
               }
           }
       }
       return $seckillGoodsResult;
    }
}
