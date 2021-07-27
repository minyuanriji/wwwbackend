<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销插件-处理经销分佣公共类
 * Author: zal
 * Date: 2020-05-29
 * Time: 18:10
 */

namespace app\plugins\area\forms\common;

use app\core\BasePagination;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\MallSetting;
use app\models\PriceLog;
use app\models\User;
use app\models\UserParent;
use app\plugins\area\models\Area;
use app\plugins\area\models\AreaAgent;
use app\plugins\area\models\AreaLevel;
use app\plugins\area\models\AreaOrder;
use app\plugins\area\models\AreaPriceLogType;
use app\plugins\area\models\AreaSetting;
use app\plugins\area\Plugin;
use app\models\BaseModel;

class Common extends BaseModel
{
    public $config;
    public $sign;
    public static $instance;
    public $mall;


    /**
     * 获取公共类
     * @param $mall
     * @return Common
     * @throws \Exception
     */
    public static function getCommon($mall)
    {
        if (self::$instance) {
            return self::$instance;
        }
        $form = new Common();
        $form->mall = $mall;
        self::$instance = $form;


        return $form;
    }

    /**
     * 获取配置
     * @return AreaSetting|null
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = AreaSetting::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new AreaSetting();
            $config->mall_id = $this->mall->id;
        }
        $this->config = $config;

        return $config;
    }

    /**
     * 获取经销用户信息
     * @param $user
     * @return array|null
     */
    public function getAreaInfo($user)
    {
        if (!$user) {
            return null;
        }
        $plugin = new Plugin();
        $sign = $plugin->getName();
        if (!$sign) {
            $this->sign = 'mall';
        } else {
            $this->sign = $sign;
        }
        $returnData = [];
        $total_price = 0;
        $frozen_price = 0;
        $yesterday_price = 0;
        $area = $this->getAreaUser($user);
        if (empty($area)) {
            $is_apply = AreaSetting::getValueByKey(AreaSetting::IS_APPLY, \Yii::$app->mall->id);
            $is_check = AreaSetting::getValueByKey(AreaSetting::IS_CHECK, \Yii::$app->mall->id);
            if ($is_apply == 0 && $is_check == 0) {
                $area = new AreaAgent();
                $area->mall_id = $user->mall_id;
                $area->user_id = $user->id;
                $area->created_at = time();
                $area->level =0;
                $area->district_id = 0;
                $area->province_id = 0;
                $area->city_id = 0;
                $area->town_id =0;
                $area->is_delete = 0;
                if ($area->save()) {
                    if (!$user->is_inviter) {
                        $user->inviter_at = time();
                        $user->save();
                    }
                }
            } else {
                return null;
            }
        }
        $is_price = '0.00';
        if (!empty($area)) {
            $total_price = $area->total_price;
            $frozen_price = floatval($area->frozen_price);
            $query = PriceLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user->id, 'is_price' => 1, 'sign' => $this->sign]);
            $is_price = $query->sum("price");
            //昨日收益
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $begin_time = strtotime($yesterday . " 00:00:00");
            $end_time = strtotime($yesterday . " 23:59:59");
            $query = PriceLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user->id, 'sign' => $this->sign]);
            $query->andWhere(['between', "created_at", $begin_time, $end_time]);
            $yesterday_price = $query->sum("price");
            $returnData['level_name'] = AreaAgent::LEVEL[$area->level];
        }

        $returnData["nickname"] = $user->nickname;
        $returnData["avatar_url"] = $user->avatar_url;
        $returnData["total_price"] = floatval($total_price);
        $returnData["frozen_price"] = $frozen_price ?? '0.00';
        $returnData["is_price"] = $is_price ?? '0.00';
        $returnData["yesterday_price"] = $yesterday_price ?? '0.00';
        return $returnData;
    }

    /**
     * 获取经销用户信息
     * @param $user
     * @return AreaAgent|null
     */
    public
    function getAreaUser($user)
    {
        if (empty($user)) {
            return null;
        }
        $area = AreaAgent::findOne(['user_id' => $user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$area) {
            return null;
        }
        return $area;
    }

    /**
     * 获取经销记录列表
     * @param $user
     * @param $params
     * @return array
     */
    public function getAreaLogList($user, $params)
    {
        $plugin = new Plugin();
        $sign = $plugin->getName();
        if (!$sign) {
            $this->sign = 'mall';
        } else {
            $this->sign = $sign;
        }
        $status = isset($params["status"]) ? $params["status"] : 0;//状态
        $page = isset($params["page"]) ? $params["page"] : 1;//页码
        $query = PriceLog::find()
            ->alias('l')
            ->leftJoin(['u' => User::tableName()], 'u.id=l.child_id')
            ->where([
                'l.sign' => $this->sign,
                'l.mall_id' => \Yii::$app->mall->id,
                'l.is_delete' => 0
            ]);
        if ($user) {
            $query->andWhere(['l.user_id' => $user->id]);
        }

        if ($status == 1) {
            $query->andWhere(['l.status' => 0, 'l.is_price' => 0]);
        }

        if ($status == 2) {
            $query->andWhere(['l.is_price' => 1]);
        }

        if ($status == 3) {
            $query->andWhere(['l.status' => -1]);
        }

        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);
        if ($page) {
            /**
             * @var BasePagination $pagination
             */
            $query = $query->page($pagination, 20, $page);
        }
        $list = $query->select('l.*,u.avatar_url,u.nickname')->orderBy('l.created_at DESC')->asArray()->all();
        foreach ($list as &$item) {
            $common_order_detail = CommonOrderDetail::findOne(['is_delete' => 0, 'id' => $item['common_order_detail_id']]);
            if (!$common_order_detail) {
                $item['goods_name'] = '未知商品';
                continue;
            } else {
                $item['goods_price'] = $common_order_detail->price;
                //需要加入商品类型
                $goods = Goods::findOne($common_order_detail->goods_id);
                if (!$goods) {
                    $item['goods_name'] = '未知商品';
                } else {
                    $item['goods_name'] = $goods->name;
                }
            }
            if ($item['status'] == 0) {
                $item['status'] = '待结算';
            }
            if ($item['status'] == 1) {
                $item['status'] = '已结算';
            }
            if ($item['status'] == -1) {
                $item['status'] = '无效';
            }

            if ($item['is_price'] == 1) {
                $item['is_price'] = '已发放';
            } else {
                $item['is_price'] = '未发放';
            }
            $item['order_no'] = $common_order_detail->order_no;
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;
        return $returnData;
    }

    /**
     * 获取经销订单列表
     * @param $order
     * @return array
     */
    public
    function getAreaOrderList($order)
    {
        $order_id = $order["id"];
        $query = AreaOrder::find()->where(["mall_id" => \Yii::$app->mall->id, "order_id" => $order_id]);
        $query = $query->orderBy("id desc");
        $list = $query->with("order")->all();
        return $list;
    }
}
