<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/12
 * Time: 9:23
 */

namespace app\plugins\group_buy\services;

use app\models\Goods;
use app\plugins\group_buy\models\PluginGroupBuyGoodsAttr;
use app\plugins\group_buy\models\PluginGroupBuyGoods;
use yii\helpers\ArrayHelper;
use Yii;
use app\plugins\group_buy\jobs\GroupBuyGoodsBeginningJob;

use app\plugins\group_buy\forms\mall\MultiActiveEditForm;

class GroupBuyGoodsServices
{
    use ReturnData;

    /**
     * 拼团商品最低规格价
     * @param $goods_id
     * @return array
     */
    public function getDispalyGroupBuyPrice($goods_id)
    {
        $goods = Goods::findOne($goods_id);
        if (!$goods) {
            return $this->returnApiResultData(98, "没有该商品");
        }

        $group_buy_goods = $this->queryGroupBuyGoodsByGoodsId($goods_id);

        if (!$group_buy_goods) {
            return $this->returnApiResultData(97, "没有该拼团商品");
        }

        $goods_attr = $goods->attr;
        $goods_attr = ArrayHelper::toArray($goods_attr);

        foreach ($goods_attr as $key => $value) {
            $one                                      = PluginGroupBuyGoodsAttr::find()->where(['attr_id' => $value['id']])->one();
            $goods_attr[$key]['gourp_buy_attr_price'] = $one['group_buy_price'];
        }

        $min_group_buy_price = $this->searchmin($goods_attr, 'gourp_buy_attr_price');

        return $this->returnApiResultData(0, "", ['min_group_buy_price' => $min_group_buy_price]);
    }

    public function queryGroupBuyGoodsByGoodsId($goods_id, $as_array = true)
    {
        return $group_buy_goods = PluginGroupBuyGoods::find()
            ->where(['goods_id' => $goods_id, 'deleted_at' => 0])
            ->asArray($as_array)
            ->one();
    }

    /**
     * 获取二位数据最大值
     * @param $arr
     * @param $field
     * @return false|mixed
     */
    private function searchmax($arr, $field) // 最小值 只需要最后一个max函数  替换为 min函数即可
    {
        if (!is_array($arr) || !$field) { //判断是否是数组以及传过来的字段是否是空
            return false;
        }

        $temp = array();
        foreach ($arr as $key => $val) {
            $temp[] = $val[$field]; // 用一个空数组来承接字段
        }

        return max($temp);  // 用php自带函数 max 来返回该数组的最大值，一维数组可直接用max函数
    }

    /**
     * 获取二位数据最大值
     * @param $arr
     * @param $field
     * @return false|mixed
     */
    private function searchmin($arr, $field) // 最小值 只需要最后一个max函数  替换为 min函数即可
    {
        if (!is_array($arr) || !$field) { //判断是否是数组以及传过来的字段是否是空
            return false;
        }

        $temp = array();
        foreach ($arr as $key => $val) {
            $temp[] = $val[$field]; // 用一个空数组来承接字段
        }

        return min($temp);  // 用php自带函数 max 来返回该数组的最大值，一维数组可直接用max函数
    }

    public function groupBuyGoodsBeginning($group_buy_goods)
    {
        $time    = new TimeServices();
        $seconds = $time->getSeconds($group_buy_goods['start_at']);

        if ($seconds > 0) {
            $queue_id = Yii::$app->queue->delay($seconds)->push(new GroupBuyGoodsBeginningJob(
                ['id' => $group_buy_goods['id']]
            ));
            return $queue_id;
        }

        return 0;
    }

    /**
     * 不需要自动结束
     * @param $group_buy_goods
     * @return int
     */
//    public function groupBuyGoodsEnd($group_buy_goods)
//    {
//        $MultiActiveEditForm = new MultiActiveEditForm();
//        $end_time            = $MultiActiveEditForm->getEndTime($group_buy_goods['vaild_time']);
//        $time                = new TimeServices();
//        $seconds             = $time->getSeconds($end_time);
//
//        if ($seconds > 0) {
//            $queue_id = Yii::$app->queue->delay($seconds)->push(new GroupBuyGoodsEndJob(
//                ['id' => $group_buy_goods['id']]
//            ));
//            return $queue_id;
//        }
//
//        return 0;
//    }

    public function groupBuyGoodsQueue($group_buy_goods)
    {
        $q1 = $this->groupBuyGoodsBeginning($group_buy_goods);
      //  $q2 = $this->groupBuyGoodsEnd($group_buy_goods);

        if ($q1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取拼团商品累计销售和金额sql
     * @param $params
     * @param bool $is_array
     * @return \app\models\BaseActiveQuery
     */
    public function queryTotalGroupBuyGoods($params, $is_array = true)
    {
        $query = PluginGroupBuyGoods::find();

        if (isset($params['group_buy_id'])) {
            $query->where(['id' => $params['group_buy_id']]);
        }

        if (isset($params['goods_id'])) {
            $query->where(['goods_id' => $params['goods_id']]);
        }

        $query->with('activeItem');

        $query->asArray($is_array);

        return $query;
    }

    /**
     * 查询单条,一般查询group_buy_id时用
     * @param $params
     * @param bool $is_array
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getOneTotalGroupBuyGoods($params, $is_array = true)
    {
        $query = $this->queryTotalGroupBuyGoods($params, $is_array);

        return $query->one();
    }

    /**
     * 查询单条,一般查询goods_id时用
     * @param $params
     * @param bool $is_array
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAllTotalGroupBuyGoods($params, $is_array = true)
    {
        $query = $this->queryTotalGroupBuyGoods($params, $is_array);

        return $query->all();
    }

    /**
     * 最大开团数
     * @param $goods_stock
     * @param $people
     * @return int
     */
    static public function getMaxActiveNum($goods_stock, $people)
    {
        return intval($goods_stock / $people);
    }
}