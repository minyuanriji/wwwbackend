<?php

namespace app\plugins\group_buy\forms\api;

use app\core\ApiCode;
use app\forms\api\goods\GoodsForm as ParentGoodsForm;
use app\helpers\SerializeHelper;
use app\models\Coupon;
use app\models\CouponCatRelation;
use app\models\CouponGoodsRelation;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCollect;
use app\models\GoodsFootmark;
use app\models\UserCoupon;
use app\plugins\group_buy\forms\mall\GroupBuyGoodsAttrQueryForm;
use app\plugins\group_buy\forms\common\ActiveQueryCommonForm;
use app\helpers\ArrayHelper;
use app\plugins\group_buy\models\PluginGroupBuyActiveItem;
use app\plugins\group_buy\models\PluginGroupBuyGoodsAttr;
use app\plugins\group_buy\services\GroupBuyGoodsServices;
use app\plugins\group_buy\models\PluginGroupBuyGoods;

class GoodsForm extends ParentGoodsForm
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['id', 'required', "on" => 'detail']
        ]);
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
            $goods = Goods::findOne(['is_delete' => 0, 'id' => $this->id, 'status' => 1, 'mall_id' => \Yii::$app->mall->id]);
            if (!$goods) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '该商品不存在或者已下架！');
            }

            $group_buy_goods = PluginGroupBuyGoods::find()->where(['goods_id' => $this->id, 'deleted_at' => 0, 'mall_id' => \Yii::$app->mall->id])->one();

            if (!$group_buy_goods) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '该拼团商品不存在或者已下架！');
            }
            //增加足迹

            if (!\Yii::$app->user->isGuest) {
                $footmark = GoodsFootmark::findOne(['user_id' => \Yii::$app->user->id, 'goods_id' => $this->id]);
                if (!$footmark) {
                    $footmark           = new GoodsFootmark();
                    $footmark->goods_id = $this->id;
                    $footmark->user_id  = \Yii::$app->user->id;
                    $footmark->mall_id  = $goods->mall_id;
                }
                $footmark->is_delete = 0;
                $footmark->save();
            }
            $this->goods                   = $goods;
            $wareHouse                     = $goods->goodsWarehouse;
            $goods_collect                 = GoodsCollect::findOne(['goods_id' => $this->id, 'is_delete' => 0, 'user_id' => \Yii::$app->user->id]);
            $info['collect']['is_collect'] = 0;
            $info['collect']['collect_id'] = 0;
            if ($goods_collect) {
                $info['collect']['is_collect'] = 1;
                $info['collect']['collect_id'] = $goods_collect->id;
            }
            $info['name']           = $wareHouse->name;
            $info['original_price'] = $wareHouse->original_price;
            $info['cover_pic']      = $wareHouse->cover_pic;
            $info['detail']         = $wareHouse->detail;
            $info['unit']           = $goods->unit;
            $info['sales']          = $goods->sales;
            $info['is_show_sales']  = $goods->is_show_sales;
            $info['use_attr']       = $goods->use_attr;
            //拼团规格
            $info['goods_stock'] = $group_buy_goods->goods_stock;

            if ($goods->use_virtual_sales) {
                $info['sales'] = $info['sales'] + $this->goods->virtual_sales;
            }
            $info['pic_list'] = SerializeHelper::decode($wareHouse->pic_url);
            if ($goods->use_attr) {
                $attr_groups         = $goods->attr_groups;
                $info['attr_groups'] = SerializeHelper::decode($attr_groups);
            }
            $this->setAttr($goods->attr);
            $info['attr_list']    = $this->attr;
            $info['service_list'] = $this->getServices();
            $info['max_price']    = $this->getPriceMax();
            $info['min_price']    = $this->getPriceMin();
            $coupon_list1         = Coupon::find()
                ->alias('c')
                ->andWhere(['c.appoint_type' => 3])
                ->andWhere(['!=', 'c.total_count', 0])
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => \Yii::$app->mall->id])
                ->asArray()
                ->all();
            $goodsWarehouse       = $goods->goodsWarehouse;

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
            $coupon_list3  = Coupon::find()
                ->alias('c')
                ->leftJoin(['cgr' => CouponGoodsRelation::tableName()], 'cgr.coupon_id=c.id')
                ->andWhere(['c.appoint_type' => 2])
                ->andWhere(['!=', 'c.total_count', 0])
                ->andWhere(['cgr.is_delete' => 0])
                ->andWhere(['c.is_delete' => 0, 'c.mall_id' => \Yii::$app->mall->id])
                ->andWhere(['cgr.goods_warehouse_id' => $goodsWarehouse->id])
                ->asArray()
                ->all();
            $coupon_list   = array_merge($coupon_list1, $coupon_list2, $coupon_list3);
            $newCouponIds  = [];
            $newCouponList = [];
            if (count($coupon_list)) {
                foreach ($coupon_list as $coupon) {
                    if (!in_array($coupon['id'], $newCouponIds)) {
                        array_push($newCouponIds, $coupon['id']);
                        $coupon['is_receive'] = 0;
                        $coupon['begin_at']   = date('Y-m-d', $coupon['begin_at']);
                        $coupon['end_at']     = date('Y-m-d', $coupon['end_at']);
                        if (!\Yii::$app->user->isGuest) {
                            $user_coupon = UserCoupon::findOne(['user_id' => \Yii::$app->user->identity->id, 'is_use' => 0, 'is_delete' => 0, 'coupon_id' => $coupon['id']]);
                            if ($user_coupon) {
                                $coupon['is_receive'] = 1;
                            }
                        }
                        $newCouponList[] = $coupon;
                    }
                }
                unset($coupon);
                unset($newCouponIds);
                unset($coupon_list);
            }

            $info['goods_activities'] = $this->getGoodsActivities();
            $info['coupon_list']      = $newCouponList;
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', ['goods' => $info]);
        } catch (\Exception $e) {
            \Yii::error($e);

            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), $this->errors);
        }
    }

    public function getGroupBuyDetail()
    {
        $this->scenario = "detail";

        if (!$this->validate()) {
            return $this->returnApiResultData(0, $this->responseErrorMsg($this));
        }

        $PluginGroupBuyGoods = new PluginGroupBuyGoods();

        $group_buy_goods = $PluginGroupBuyGoods->getGroupBuyGoodsOne($this->id, \Yii::$app->mall->id);

        if (!$group_buy_goods) {
            return $this->returnApiResultData(99, "拼团商品不存在");
        }

        $return = $this->getDetail();

        $goods =& $return['data']['goods'];

        //累计销售额
        $goods['group_buy']['cumulative_sales'] = $this->getCumulativeSales($this->id);
        //拼团人数
        $group_buy_goods              = $this->getGroupBuyGoods($this->id);
        $goods['group_buy']['people'] = $group_buy_goods['people'];
        //拼团价格
        $goods['group_buy']['group_buy_price'] = $this->getMinGroupBuyPrice($this->id);

        foreach ($goods['attr_list'] as $key => $value) {
            $group_buy_goods_attr                        = PluginGroupBuyGoodsAttr::find()->where(['attr_id' => $value['id']])->one();
            $goods['attr_list'][$key]['group_buy_price'] = GroupBuyGoodsAttrQueryForm::getGroupBuyPriceByAttrId($value['id']);
            $goods['attr_list'][$key]['stock']           = $group_buy_goods_attr->stock;
        }

        return $return;
    }

    /**
     * @param $goods_id
     * @return int|mixed
     */
    public function getMinGroupBuyPrice($goods_id)
    {
        $GroupBuyGoodsServices          = new GroupBuyGoodsServices();
        $return_dispaly_group_buy_price = $GroupBuyGoodsServices->getDispalyGroupBuyPrice($goods_id);

        if ($return_dispaly_group_buy_price['code'] == 0) {
            return $return_dispaly_group_buy_price['data']['min_group_buy_price'];
        }
        return 0;
    }

    public function getGroupBuyGoods($goods_id)
    {
        $GroupBuyGoodsServices = new GroupBuyGoodsServices();

        return $GroupBuyGoodsServices->queryGroupBuyGoodsByGoodsId($goods_id);
    }

    //已团多少件
    public function getCumulativeSales($goods_id)
    {
        $ActiveQueryCommonForm           = new ActiveQueryCommonForm();
        $ActiveQueryCommonForm->goods_id = $goods_id;
        $return_sum                      = $ActiveQueryCommonForm->getActualPeopleSum();

        if ($return_sum['code'] == 0) {
            return $return_sum['data']['actual_people'];
        }

        return 0;
    }
}