<?php

namespace app\forms\api\order;

use app\core\ApiCode;
use app\forms\common\order\OrderClerkCommon;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Cart;
use app\models\clerk\ClerkData;
use app\models\ClerkUser;
use app\models\Mall;
use app\models\Order;
use app\models\User;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;

class OrderCheckDiscountTypeForm extends BaseModel
{
    public $cart_list;

    public function rules()
    {
        return [
            [['cart_list'], 'required'],
            [['cart_list'], 'string'],
        ];
    }

    public function check()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $returnDate = [
                'use_score' => 0,
                'use_red_envelopes' => 0,
                'use_shopping_voucher' => 0,
            ];
            if (is_string($this->cart_list)) {
                $cart_list = explode(',', $this->cart_list);
            } else {
                $cart_list = $this->cart_list;
            }

            $score = [];
            $red_envelopes = [];
            $goods_id = [];
            $cart = Cart::find()->andWhere(['and', ['in', 'id', $cart_list]])->all();
            if ($cart) {
                foreach ($cart as $item) {
                    $score[] = $item->goods->forehead_score;
                    $red_envelopes[] = $item->goods->max_deduct_integral;
                    $goods_id[] = $item->goods_id;
                }
            }
            $max_score = max($score);
            $max_red_envelopes = max($red_envelopes);

            $user = User::findOne(\Yii::$app->user->id);
            if (!$user || $user->is_delete)
                throw new \Exception('用户不存在');

            $targetGoods = ShoppingVoucherTargetGoods::find()
                ->andWhere(['and', ['in', 'goods_id', $goods_id], ['is_delete' => 0]])
                ->all();

            $user_voucher = ShoppingVoucherUser::findOne(['user_id' => \Yii::$app->user->id]);

            //判断积分是否可以使用
            if (($user->score > 0 || $user->total_score > 0 || $user->static_score > 0) && $max_score > 0) {
                $returnDate['use_score'] = 1;
            }

            //判断金豆是否可以使用
            if (($user->static_integral > 0 || $user->dynamic_integral > 0) && $max_red_envelopes > 0) {
                $returnDate['use_red_envelopes'] = 1;
            }

            //判断红包是否可以使用
            if ($user_voucher && $user_voucher->money > 0 && $targetGoods) {
                $returnDate['use_shopping_voucher'] = 1;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', $returnDate);
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
