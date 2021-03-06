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
use yii\helpers\ArrayHelper;

class CartAddForm extends BaseModel
{
    public $goods_id;
    public $attr;
    public $num;
    public $mch_id;

    public function rules()
    {
        return [
            [['goods_id', 'attr', 'num'], 'required'],
            [['goods_id', 'num', 'attr', 'mch_id'], 'integer'],
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
                'g.id' => $this->attr,
                'g.goods_id' => $this->goods_id,
                'g.is_delete' => 0,
            ])->innerJoinwith(['goods o' => function ($query) {
                $query->where([
                    'o.id' => $this->goods_id,
                    'o.mall_id' => \Yii::$app->mall->id,
                    'o.is_delete' => 0,
                    'o.status' => Goods::STATUS_ON,
                ]);
            }])->one();

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
                $cart->mall_id = \Yii::$app->mall->id;
                $cart->user_id = \Yii::$app->user->id;
                $cart->goods_id = $this->goods_id;
                $cart->attr_id = $this->attr;
                $cart->num = 0;
                $cart->mch_id = $goods->mch_id;
                $cart->attr_info = \Yii::$app->serializer->encode(ArrayHelper::toArray($attr));
            }
            $cart->num += $this->num;
            if ($cart->save()) {
                \Yii::$app->trigger(Cart::EVENT_CART_ADD, new CartEvent(['cartIds' => [$cart->id]]));
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"加入购物车成功");
            } else {
                return $this->returnApiResultData(999,"",$cart);
            }
        } catch (\Exception $e) {
            //Cart::cacheStatusSet(false);
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}
