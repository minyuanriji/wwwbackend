<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-13
 * Time: 17:16
 */

namespace app\forms\common\goods;

use app\core\ApiCode;
use app\events\GoodsEvent;
use app\forms\common\mch\MchSettingForm;
use app\forms\common\mptemplate\MpTplMsgSend;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\MallGoods;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchGoods;

class GoodsBase extends BaseModel
{
    public $id;
    public $goods_id;
    public $search;
    public $page;
    public $status;
    public $sort;
    public $batch_ids = [];
    public $is_all;
    public $freight_id;
    public $goods_name;
    public $give_score;
    public $give_score_type;
    public $forehead_score;
    public $forehead_score_type;
    public $accumulative;
    public $continue_goods_count;
    public $continue_order_count;
    public $is_goods_confine;
    public $is_order_confine;
    public $goods_price_type;
    public $goods_price;
    public $goods_price_update_type;
    public $cat_id;
    public $MallMemberLevel = [];
    /**
     * @var GoodsCommon
     */
    public $goods;
    public $plugin_sign;


    public function rules()
    {
        return [
            [['id', 'status', 'goods_id', 'sort', 'freight_id', 'cat_id',
                'give_score', 'give_score_type', 'forehead_score', 'forehead_score_type',
                'accumulative', 'is_all', 'is_goods_confine', 'is_order_confine', 'continue_goods_count',
                'continue_order_count', 'goods_price_type', 'goods_price_update_type'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['freight_id'], 'default', 'value' => 0],
            [['search', 'goods_name', 'plugin_sign', 'goods_price'], 'string'],
            [['batch_ids'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '角色ID',
        ];
    }

    public function findGoods()
    {
        $goods = Goods::find()->with('goodsWarehouse')->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id,
            'is_delete' => 0,
        ])->one();

        if (!$goods) {
            throw new \Exception('商品不存在');
        }
        $this->goods = $goods;
    }

    public function delete()
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            $this->findGoods();
            $goods = $this->goods;
            if (!$goods) {
                throw new \Exception('商品不存在');
            }
            $goods->is_delete = 1;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($goods));
            }
            if ($goods->sign == '') {
                $goods->goodsWarehouse->is_delete = 1;
                if (!$goods->goodsWarehouse->save()) {
                    throw new \Exception($this->responseErrorMsg($goods->goodsWarehouse));
                }
                \Yii::$app->trigger(Goods::EVENT_DESTROY, new GoodsEvent(['goods' => $goods]));
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function switchStatus()
    {
        try {
            $goods = Goods::findOne([
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->id,
                'is_delete' => 0
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->status = $goods->status ? 0 : 1;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($goods));
            }

            $this->setMchGoodsStatus($goods);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    // 更新多商户商品上架状态
    private function setMchGoodsStatus($goods)
    {
        if (\Yii::$app->admin->identity->mch_id > 0) {
            $mchGoods = MchGoods::findOne(['goods_id' => $goods->id]);
            if (!$mchGoods) {
                throw new \Exception('商品不存在');
            }
            if ($goods->status) {
                $mchGoods->status = 2;
            } else {
                $mchGoods->status = 0;
            }
            $mchGoods->remark = '';
            $res = $mchGoods->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($mchGoods));
            }
        }
    }

    public function switchQuickShop()
    {
        try {
            $goods = MallGoods::findOne([
                'goods_id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    public function editSort()
    {
        try {
            /** @var GoodsCommon $goods */
            $goods = Goods::findOne([
                'id' => $this->id,
                'mall_id' => \Yii::$app->mall->id,
            ]);

            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->sort = $this->sort;
            $res = $goods->save();
            if (!$res) {
                throw new \Exception($this->responseErrorMsg($goods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

    // 批量删除
    public function batchDestroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'sign' => $this->plugin_sign,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];
        }

        $res = Goods::updateAll([
            'is_delete' => 1
        ], $where);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功',
            'data' => [
                'num' => $res
            ]
        ];
    }


    public function batchUpdateStatus()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $isGoodsAudit = 1;
        try {
          /*  $mchPlugin = \Yii::$app->plugin->getPlugin('mch');
            if ($mchPlugin) {
                $mchSetting = (new MchSettingForm())->search();
                $isGoodsAudit = $mchSetting['is_goods_audit'];
            }*/
        } catch (\Exception $exception) {
        }

        // 是多商户 并且 商品需要审核
        if (\Yii::$app->admin->identity->mch_id > 0 && $isGoodsAudit) {
            $res = $this->setMchGoodsApplyStatus();
        } else {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }

            $res = Goods::updateAll(['status' => $this->status], $where);

            // 如果是多商户 则需更新商户商品状态
            $this->updateMchGoodsStatus();
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateFreight()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'sign' => $this->plugin_sign,
                'is_delete' => 0,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'sign' => $this->plugin_sign,
                'id' => $this->batch_ids,
            ];
        }
        $res = Goods::updateAll(['freight_id' => $this->freight_id], $where);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '更新成功',
            'data' => [
                'num' => $res
            ]
        ];
    }

    public function batchUpdateConfineCount()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }

            if ($this->continue_goods_count < 0 && !$this->is_goods_confine) {
                throw new \Exception('限购商品数量不能小于0');
            }

            if ($this->continue_order_count < 0 && !$this->is_order_confine) {
                throw new \Exception('限购订单数量不能小于0');
            }

            $goodsCount = (int)$this->continue_goods_count;
            if ($this->is_goods_confine || $goodsCount < 0) {
                $goodsCount = -1;
            }

            $orderCount = (int)$this->continue_order_count;
            if ($this->is_order_confine || $orderCount < 0) {
                $orderCount = -1;
            }

            $res = Goods::updateAll(['confine_count' => $goodsCount, 'confine_order_count' => $orderCount], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    // 批量更新赠送积分
    public function batchUpdateIntegral()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'sign' => $this->plugin_sign,
                    'id' => $this->batch_ids,
                ];
            }

            if ($this->give_score < 0) {
                throw new \Exception('积分赠送不能小于0');
            }

            if ($this->forehead_score < 0) {
                throw new \Exception('积分抵扣不能小于0');
            }

            $res = Goods::updateAll([
                'give_score' => (int)$this->give_score <= 0 ? 0 : $this->give_score,
                'give_score_type' => $this->give_score_type == 1 ? $this->give_score_type : 2,
                'forehead_score' => $this->forehead_score <= 0 ? 0 : $this->forehead_score,
                'forehead_score_type' => $this->forehead_score_type == 1 ? $this->forehead_score_type : 2,
                'accumulative' => $this->accumulative == 1 ? $this->accumulative : 0,
            ], $where);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $res
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function updateGoodsName()
    {
        try {
            /** @var GoodsCommon $goods */
            $goods = Goods::find()->where(['id' => $this->goods_id])->with('goodsWarehouse')->one();
            if (!$goods) {
                throw new \Exception('商品不存在');
            }

            $goods->goodsWarehouse->name = $this->goods_name;
            $res = $goods->goodsWarehouse->save();

            if (!$res) {
                throw new \Exception($this->responseErrorMsg($goods->goodsWarehouse));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
            ];

        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

    private function setMchGoodsApplyStatus()
    {
        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'is_delete' => 0,
                'status' => [0, 3]
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'goods_id' => $this->batch_ids,
                'status' => [0, 3]
            ];
        }

        $res = MchGoods::updateAll([
            'status' => 1,
            'remark' => '申请上架'
        ], $where);

        // 有更新再发送模板消息
        if ($res) {
            $this->sendMpTplMsg();
        }

        return $res;
    }

    /**
     *  更改多商户商品状态
     */
    private function updateMchGoodsStatus()
    {
        if (\Yii::$app->admin->identity->mch_id > 0) {
            if ($this->is_all) {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'is_delete' => 0,
                ];
            } else {
                $where = [
                    'mall_id' => \Yii::$app->mall->id,
                    'mch_id' => \Yii::$app->admin->identity->mch_id,
                    'goods_id' => $this->batch_ids,
                ];
            }
            if ($this->status) {
                $res = MchGoods::updateAll([
                    'status' => 2,
                    'remark' => ''
                ], $where);
            } else {
                $res = MchGoods::updateAll([
                    'status' => 0,
                    'remark' => '申请上架'
                ], $where);
            }
        }
    }

    /**
     * 发给管理员公众号消息
     */
    private function sendMpTplMsg()
    {
        try {
            try {
                $mch = Mch::findOne(\Yii::$app->admin->identity->mch_id);
                $mchName = $mch->store->name;
            } catch (\Exception $exception) {
                $mchName = '商城商户';
            }

            $tplMsg = new MpTplMsgSend();
            $tplMsg->method = 'mchGoodApplyTpl';
            $tplMsg->params = [
                'goods' => '商户：' . $mchName . '商品申请上架'
            ];
            $tplMsg->sendTemplate(new MpTplMsgSend());
        } catch (\Exception $exception) {
            \Yii::error('公众号模板消息发送: ' . $exception->getMessage());
        }
    }

    public function batchUpdateGoodsPrice()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if ($this->is_all) {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'is_delete' => 0,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'sign' => $this->plugin_sign,
            ];
        } else {
            $where = [
                'mall_id' => \Yii::$app->mall->id,
                'id' => $this->batch_ids,
                'mch_id' => \Yii::$app->admin->identity->mch_id,
                'sign' => $this->plugin_sign,
            ];
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $goods = Goods::find()->where($where)->with('attr')->all();
            $count = 0;
            /** @var GoodsCommon $item */
            foreach ($goods as $item) {
                $item->price = $this->getNewPrice($item->price);
                $res = $item->save();
                if (!$res) {
                    throw new \Exception($this->responseErrorMsg($item));
                }

                foreach ($item->attr as $aItem) {
                    $newPrice = $this->getNewPrice($aItem->price);
                    $aItem->price = $newPrice;
                    $res = $aItem->save();
                    if (!$res) {
                        throw new \Exception($this->responseErrorMsg($aItem));
                    }
                }
                $count += 1;

                $plugin = \Yii::$app->plugin->getPlugin($item->sign);
                // 判断插件是否有hasVideoGoodsList这个方法，没有的则使用商城的
                if (method_exists($plugin, 'updateGoodsPrice')) {
                    $res = $plugin->updateGoodsPrice($item);
                }
            }
            $transaction->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '更新成功',
                'data' => [
                    'num' => $count
                ]
            ];
        } catch (\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage(),
                'error' => [
                    'line' => $exception->getLine()
                ]
            ];
        }
    }

    protected function getNewPrice($newPrice)
    {
        if ($this->goods_price_type == 1) {
            if ($this->goods_price_update_type == 1) {
                // 固定金额 提高
                $newPrice += $this->goods_price;
            } elseif ($this->goods_price_update_type == 2) {
                // 固定金额 降低
                $newPrice -= $this->goods_price;
            }
        } elseif ($this->goods_price_type == 2) {
            if ($this->goods_price_update_type == 1) {
                // 百分比 提高
                $newPrice += $newPrice * ($this->goods_price / 100);
            } elseif ($this->goods_price_update_type == 2) {
                // 百分比 降低
                $newPrice -= $newPrice * ($this->goods_price / 100);
            }
        }

        $maxPrice = '99999999.00';
        $minPrice = '0';
        $newPrice = $newPrice > $maxPrice ? $maxPrice : $newPrice;
        $newPrice = $newPrice < $minPrice ? $minPrice : $newPrice;

        return $newPrice;
    }
}
