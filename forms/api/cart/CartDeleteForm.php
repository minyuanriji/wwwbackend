<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 购物车api-删除购物车
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\cart;

use app\core\ApiCode;
use app\events\CartEvent;
use app\models\BaseModel;
use app\models\Cart;

class CartDeleteForm extends BaseModel
{
    public $cart_id_list;

    public function rules()
    {
        return [
            [['cart_id_list'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }

        try {
            Cart::cacheStatusSet(true);
            $this->cart_id_list = json_decode($this->cart_id_list, true);
            Cart::updateAll(['is_delete' => 1, 'deleted_at' => date('Y-m-d H:i:s')], [
                'id' => $this->cart_id_list,
                'is_delete' => 0,
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => \Yii::$app->admin->id,
            ]);
            Cart::cacheStatusSet(false);
            //购物单
            \Yii::$app->trigger(Cart::EVENT_CART_DESTROY, new CartEvent(['cartIds' => $this->cart_id_list]));
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => 'success',
            ];
        } catch (\Exception  $e) {
            Cart::cacheStatusSet(false);
        }
    }
}
