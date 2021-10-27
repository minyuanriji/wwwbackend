<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 购物车api-购物车
 * Author: zal
 * Date: 2020-04-22
 * Time: 14:50
 */

namespace app\forms\api\cart;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Cart;
use app\models\Goods;
use app\models\MemberLevel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\Plugin;
use yii\helpers\ArrayHelper;

class CartForm extends BaseModel
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['limit'], 'integer'],
            [['limit'], 'default', 'value' => 10],
        ];
    }

    /**
     * 购物车列表
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 16:33
     * @return array
     */
    public function getCartList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {
            $user_id = \Yii::$app->user->id;

            //查询条件
            $wheres = [
                'mall_id' => \Yii::$app->mall->id,
                'user_id' => $user_id,
                'sign' => '',
            ];
            $list = Cart::getList($wheres);
//            $user = User::findOne(['id' => $user_id]);
            $newList = [];

            /** @var Cart[] $list */
            foreach ($list as $item) {
                $newItem = ArrayHelper::toArray($item);
                $goods = ArrayHelper::toArray($item->goods);
                $newItem['not_can_buy_reason']='';
                $newItem['is_not_can_buy'] =0;
                if ($goods['confine_order_count'] > -1) {
                    $count = OrderDetail::find()->alias('od')
                        ->select('od.order_id')
                        ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                        ->where([
                            'o.cancel_status' => 0,
                            'o.is_delete' => 0,
                            'o.is_recycle' => 0,
                            'o.user_id' => \Yii::$app->user->identity->id,
                            'od.is_delete' => 0,
                            'od.goods_id' => $goods['id'],
                        ])
                        ->groupBy('od.order_id')
                        ->count();
                    if ($count >= $goods['confine_order_count']) {
                        $newItem['is_not_can_buy'] =1;
                        $newItem['not_can_buy_reason'] = "该商品限购{$count}单";
                    }
                }
                if ($goods['confine_count'] > -1) {
                    $count = OrderDetail::find()->alias('od')
                        ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
                        ->where([
                            'o.cancel_status' => 0,
                            'o.is_delete' => 0,
                            'o.is_recycle' => 0,
                            'o.user_id' => \Yii::$app->user->identity->id,
                            'od.is_delete' => 0,
                            'od.goods_id' => $goods['id'],
                        ])
                        ->sum('num');
                    if ($count >= $goods['confine_count']) {
                        $newItem['is_not_can_buy'] =1;
                        $newItem['not_can_buy_reason'] = "该商品限购{$count}件";
                    }
                }

                $goods = $this->unsetGoodsField($goods);
                $newItem["cart_id"] = $item["id"];
                $newItem['goods'] = $goods;

                //$newItem['store'] = ArrayHelper::toArray($item->store);
                $newItem['attrs'] = $item->attrs ? ArrayHelper::toArray($item->attrs) : $item->attrs;
                //优惠价
                //$newItem['reduce_price'] = 0;
                if ($item->attrs) {
                    //商品规格
                    $newItem['attrs']['attr'] = (new Goods())->signToAttr($item->attrs->sign_id, $item->goods->attr_groups);
                    //$newItem['attr_str'] = 0;
                    if ($item->attr_info) {
                        try {
                            $attrInfo = \Yii::$app->serializer->decode($item->attr_info);
                            $reducePrice = $attrInfo['price'] - $item->attrs->price;
                            if ($attrInfo['price'] - $item->attrs->price) {
                                $newItem['reduce_price'] = $reducePrice;
                            }
                        } catch (\Exception $exception) {
                        }
                    }
                } else {
                    $newItem['attrs']['stock'] = 0;
                    //$newItem['attr_str'] = 1;
                }
                $newItem['goods']['name'] = $item->goods->name;
                $newItem['goods']['cover_pic'] = $item->goods->coverPic;
                $newItem['goods']['status'] = $item->goods->status;

                // 购物车显示会员价
//            if ($user && $user->level && $item->goods->is_level && $item->mch_id == 0 && $item->attrs) {
//                if ($item->goods->is_level_alone) {
//                    foreach ($item->attrs->memberPrice as $mItem) {
//                        if ($mItem->level == $user->level) {
//                            $newItem['attrs']['price'] = $mItem['price'] > 0 ? $mItem['price'] : $item->attrs->price;
//                            break;
//                        }
//                    }
//                } else {
//                    /** @var MemberLevel $memberLevel */
//                    $memberLevel = MemberLevel::find()->where([
//                        'status' => 1,
//                        'is_delete' => 0,
//                        'level' => $user->level,
//                        'mall_id' => \Yii::$app->mall->id
//                    ])->one();
//                    if ($memberLevel) {
//                        $newItem['attrs']['price'] = round(($memberLevel->discount / 10) * $item->attrs->price, 2);
//                    }
//                }
//            }

                $newList[] = $newItem;
            }

            //加入插件商品
            //$this->getPluginGoods($newList);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList
                ],
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    /**
     * 获取新的商品状态
     * @param $item
     * @return mixed
     */
    private function getNewStatus($item)
    {
        $item['is_active'] = false;
        $item['new_status'] = 0;// 正常
        // 秒杀已结束
        if ($item['sign'] == 'seckill' && $item['seckill_status'] != 1) {
            $item['new_status'] = 1;
        }
        // 商品已售罄
        if ($item['attrs'] && $item['attrs']['stock'] == 0) {
            $item['new_status'] = 2;
        }
        // 商品已失效
        if (!$item['attrs']) {
            $item['new_status'] = 3;
        }
        // 商户已关闭
        if (isset($item['mch_status']) && $item['mch_status']) {
            $item['new_status'] = 4;
        }
        // 商品已下架
        if ($item['goods']['status'] == 0) {
            $item['new_status'] = 5;
        }

        return $item;
    }

    /**
     * 加入插件商品
     * @param $newList
     * @return array
     */
    private function getPluginGoods($newList)
    {
        $list = $newList;
        $plugins = \Yii::$app->plugin->list;
        foreach ($plugins as $plugin) {
            $PluginClass = 'app\\plugins\\' . $plugin->name . '\\Plugin';
            /** @var Plugin $pluginObject */
            if (!class_exists($PluginClass)) {
                continue;
            }
            $object = new $PluginClass();
            if (method_exists($object, 'getCartList')) {
                $list = array_merge($list, $object->getCartList());
            }
        }
        // 将数据按商城 商户区分
        $newDataList = [];
        foreach ($list as $item) {
            $newDataList[$item['mch_id']][] = $item;
        }

        $newList = [];
        foreach ($newDataList as $key => $item) {
            if ($key > 0) {
                $mch = Mch::findOne([
                    'mall_id' => \Yii::$app->mall->id,
                    'is_delete' => 0,
                    'status' => 1,
                    'review_status' => 1,
                    'id' => $key
                ]);
                $newItemList = [
                    'mch_id' => $key,
                    'is_active' => false,
                    'new_status' => 0,
                    'name' => isset($item[0]['store']) ? $item[0]['store']['name'] : '未知商户',
                    'goods_list' => [],
                ];

                foreach ($item as $gItem) {
                    $gItem['mch_status'] = !$mch ? 1 : 0;
                    $newGoods = $this->getNewStatus($gItem);
                    $newItemList['goods_list'][] = $newGoods;
                }
                $newList[] = $newItemList;
            } else {
                $newItemList = [
                    'mch_id' => 0,
                    'is_active' => false,
                    'new_status' => 0,
                    'name' => \Yii::$app->mall->name ?: '平台自营',
                    'goods_list' => []
                ];
                foreach ($item as $gItem) {
                    $newGoods = $this->getNewStatus($gItem);
                    $newItemList['goods_list'][] = $newGoods;
                }
                $newList[] = $newItemList;
            }
        }
        return $newList;
    }

    /**
     * 过滤不用显示的商品字段
     * @param $goods
     * @param array $disFields
     * @return array
     */
    private function unsetGoodsField($goods, $disFields = ["price", "goods_stock", "attr_groups", "goods_warehouse_id"])
    {
        $goodsModel = new Goods();
        $field = $goodsModel->attributes;
        $returnData = [];
        foreach ($field as $key => $item) {
            if (in_array($key, $disFields)) {
                $returnData[$key] = $goods[$key];
            }
        }
        return $returnData;
    }
}
