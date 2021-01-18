<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 购物车api-购物车操作
 * Author: zal
 * Date: 2020-04-21
 * Time: 14:50
 */

namespace app\forms\api\cart;

use app\core\ApiCode;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Cart;
use app\models\GoodsAttr;

class CartEditForm extends BaseModel
{
    public $list;

    public function rules()
    {
        return [
            [['list'], 'trim'],
        ];
    }

    /**
     * 修改购物车
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @return array
     */
    public function modifyCart()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $info = $this->list;
            if (!$info) {
                throw new \Exception('数据为空');
            }

            $user_id = \Yii::$app->user->id;

            //去重
            CommonLogic::removalRepeatArrayByKey($info, 'attr', $list);

            $array = [];

            foreach ($list as $item) {
                $goodsAttr = $this->getGoodsAttr($item);
                if (!$goodsAttr) {
                    continue;
                }
                $cart = Cart::findOne([
                    'goods_id' => $item['goods_id'],
                    'user_id' => $user_id,
                    'attr_id' => $item['attr'],
                    'is_delete' => 0
                ]);

                if (!empty($cart) && $item['num'] > 0) {
                    $cart->num = $item['num'];
                    $cart->save();
                    continue;
                } elseif (!empty($cart) && $item['num'] == 0) {
                    $cart->is_delete = Cart::IS_DELETE_YES;
                    $cart->save();
                    continue;
                }

                if ($item['num'] > 0) {
                    $array[] = [
                        \Yii::$app->mall->id,
                        $user_id,
                        $item['attr'],
                        $item['goods_id'],
                        $item['num'],
                        0,
                        time(),
                        0,
                        0,
                    ];
                }
            }
            if (!empty($array)) {
                Cart::batchAdd($array);
            }
            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"success");
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 商品规格
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 18:33
     * @param $item
     * @return array|null|\yii\db\ActiveRecord
     */
    protected function getGoodsAttr($item)
    {
        return GoodsAttr::find()->alias('c')->where([
            'c.goods_id' => $item['goods_id'],
            'c.id' => $item['attr'],
            'c.is_delete' => 0,
        ])->innerJoinWith('goods')->one();
    }
}
