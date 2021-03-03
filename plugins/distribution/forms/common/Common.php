<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销插件-处理分销分佣公共类
 * Author: zal
 * Date: 2020-05-29
 * Time: 18:10
 */

namespace app\plugins\distribution\forms\common;

use app\core\BasePagination;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\MallSetting;
use app\models\PriceLog;
use app\models\User;
use app\models\user\User as UserModel;
use app\models\UserParent;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionLog;
use app\plugins\distribution\models\DistributionOrder;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\models\RebuyPriceLog;
use app\plugins\distribution\models\SubsidyPriceLog;
use app\plugins\distribution\models\TeamPriceLog;
use app\plugins\distribution\Plugin;
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
     * @return DistributionSetting|null
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = DistributionSetting::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new DistributionSetting();
            $config->mall_id = $this->mall->id;
        }
        $this->config = $config;

        return $config;
    }

    /**
     * 获取分销用户信息
     * @param $user
     * @return array|null
     */
    public function getDistributionInfo($user)
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
        $level = 0;
        $frozen_price = 0;
        $yesterday_price = 0;
        $distribution = $this->getDistributionUser($user);
        $level_name = '普通用户';
        $is_price = '0.00';
        if (!empty($distribution)) {
            $total_price = $distribution->total_price;
            $level_name = isset($distribution->distributionLevel) ? $distribution->distributionLevel->name : "普通用户";
            $frozen_price = floatval($distribution->frozen_price);
            $query = PriceLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user->id, 'is_price' => 1, 'sign' => $this->sign]);
            $is_price = $query->sum("price");

            //昨日收益
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $begin_time = strtotime($yesterday . " 00:00:00");
            $end_time = strtotime($yesterday . " 23:59:59");
            $query = PriceLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => $user->id, 'sign' => $this->sign]);
            $query->andWhere(['between', "created_at", $begin_time, $end_time]);
            $yesterday_price = $query->sum("price");
        }
        $userParent = UserParent::findOne(['user_id' => $user->id, 'is_delete' => 0, 'level' => 1]);
        if (empty($userParent)) {
            $returnData['parent_name'] = '平台';
            $returnData['parent_avatar_url'] = MallSetting::getValueByKey('logo', \Yii::$app->mall->id);
            $returnData['mobile'] = MallSetting::getValueByKey('contact_tel', \Yii::$app->mall->id);
            $returnData['parent_level_name'] = '平台方';
            $db = \yii::$app->db;
//            $sql = "select id,parent_id from jxmall_user where id = {$user->id}";
//            $result = $db -> createCommand($sql) -> queryOne();
//            if(!empty($result)){
//                $db -> createCommand() -> insert('jxmall_user_parent',[
//                    'id' => null,
//                    'mall_id' => $this->mall->id,
//                    'user_id' => $result['id'],
//                    'parent_id' => $result['parent_id'],
//                    'updated_at' => time(),
//                    'created_at' => time(),
//                    'deleted_at' => 0,
//                    'is_delete' => 0,
//                    'level' => 1
//                ]) -> execute();
//                if(!empty($result['parent_id'])) {
//                    $parent = User::findOne($result['parent_id']);
//                    if ($parent) {
//                        $returnData['parent_name'] = $parent->nickname;
//                        $returnData['avatar_url'] = $parent->avatar_url;
//                        $returnData['mobile'] = $parent->mobile;
//                        $returnData['parent_level_name'] = '普通分销商';
//                        $distributionParent = $this->getDistributionUser($parent);
//                        if ($distributionParent) {
//                            $parent_level_name = isset($distributionParent->distributionLevel) ? $distributionParent->distributionLevel->name : "普通分销商";
//                            $returnData['parent_level_name'] = $parent_level_name;
//                        }
//                    }
//                }
//            }
        } else {
            $parent = User::findOne($userParent->parent_id);
            if ($parent) {
                $returnData['parent_name'] = $parent->nickname;
                $returnData['avatar_url'] = $parent->avatar_url;
                $returnData['mobile'] = $parent->mobile;
                $returnData['parent_level_name'] = '普通分销商';
                $distributionParent = $this->getDistributionUser($parent);
                if ($distributionParent) {
                    $parent_level_name = isset($distributionParent->distributionLevel) ? $distributionParent->distributionLevel->name : "普通分销商";
                    $returnData['parent_level_name'] = $parent_level_name;
                }
            }
        }

        $is_rebuy = DistributionSetting::getValueByKey(DistributionSetting::IS_REBUY, \Yii::$app->mall->id);
        $returnData["is_rebuy"] = $is_rebuy ?? 0;
        if ($is_rebuy) {
            $rebuy_total_price = RebuyPriceLog::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0])->sum('price');
            $rebuy_total_price = $rebuy_total_price ?? 0;
            $month = date('Y-m', strtotime('-1 month'));
            $last_total_price = RebuyPriceLog::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0, 'month' => $month])->sum('price');
            $last_total_price = $last_total_price ?? 0;
            $returnData["rebuy_total_price"] = $rebuy_total_price;
            $returnData["rebuy_last_price"] = $last_total_price;
        }


        $is_subsidy = DistributionSetting::getValueByKey(DistributionSetting::IS_SUBSIDY, \Yii::$app->mall->id);
        $returnData["is_subsidy"] = $is_subsidy ?? 0;
        if ($is_subsidy) {
            $subsidy_total_price = SubsidyPriceLog::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0])->sum('price');
            $subsidy_total_price = $subsidy_total_price ?? 0;
            $month = date('Y-m', strtotime('-1 month'));
            $last_total_price = SubsidyPriceLog::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0, 'month' => $month])->sum('price');
            $last_total_price = $last_total_price ?? 0;
            $returnData["subsidy_total_price"] = $subsidy_total_price;
            $returnData["subsidy_last_price"] = $last_total_price;
        }
        $is_team = DistributionSetting::getValueByKey(DistributionSetting::IS_TEAM, \Yii::$app->mall->id);
        $returnData["is_team"] = $is_team ?? 0;
        if ($is_team) {
            $team_total_price = TeamPriceLog::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0])->sum('price');
            $team_total_price = $team_total_price ?? 0;
            $begin_at = date('Y-m-01 00:00:01', strtotime('-1 month'));
            $end_at = date('Y-m-01 00:00:01', time());

            $last_total_price = TeamPriceLog::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0])->andWhere(['>','created_at',$begin_at])->andWhere(['<','created_at',$end_at])->sum('price');
            $last_total_price = $last_total_price ?? 0;
            $returnData["team_total_price"] = $team_total_price;
            $returnData["team_last_price"] = $last_total_price;
        }

        $returnData["is_distribution"] = empty($distribution) ? 0 : 1;
        $returnData["total_price"] = floatval($total_price);
        $returnData["level_name"] = $level_name;
        $returnData["frozen_price"] = $frozen_price ?? '0.00';
        $returnData["is_price"] = $is_price ?? '0.00';
        $returnData["yesterday_price"] = $yesterday_price ?? '0.00';
        return $returnData;
    }

    /**
     * 获取分销用户信息
     * @param $user
     * @return Distribution|null
     */
    public function getDistributionUser($user)
    {
        if (empty($user)) {
            return null;
        }
        $distribution = Distribution::findOne(['user_id' => $user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$distribution) {
            return null;
        }
        return $distribution;
    }

    /**
     * 获取分销记录列表
     * @param $user
     * @param $params
     * @return array
     */
    public function getDistributionLogList($user, $params)
    {

        $plugin = new Plugin();
        $sign = $plugin->getName();
        if (!$sign) {
            $this->sign = 'mall';
        } else {
            $this->sign = $sign;
        }
        $level = isset($params["level"]) ? $params["level"] : null;//分销层级
        $is_price = isset($params['is_price']) ? $params['is_price'] : -1;//分佣状态
        $page = isset($params["page"]) ? $params["page"] : 1;//分销层级
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
        if ($is_price != -1) {
            if ($is_price == 0) {
                $query->andWhere(['l.is_price' => 0]); //未结算
            }
            if ($is_price == 1) {
                $query->andWhere(['l.is_price' => 1]); //已结算
            }
        }
        if ($level) {
            //直推
            if ($level == 1) {
                $query->andWhere(['l.level' => 1]);
            }
            //间推
            if ($level == 2) {
                $query->andWhere(['>', 'l.level', 1]);
            }
        }
        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);

        if ($page) {
            /**
             * @var BasePagination $pagination
             */
            $query = $query->page($pagination, 20, $page);
        }
        $list = $query->select('l.*,u.avatar_url,u.nickname,l.child_id as user_id')->orderBy('l.created_at DESC')->asArray()->all();
       // dd($list);
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
            $item['order_no'] = $common_order_detail->order_no;
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;

        return $returnData;
    }


    /**
     * 获取分销商复购奖励记录列表
     * @param $user
     * @param $params
     * @return array
     */
    public function getRebuyPriceList($user, $params)
    {
        $page = isset($params["page"]) ? $params["page"] : 1;//分销层级
        $query = RebuyPriceLog::find()
            ->alias('l')
            ->where([
                'l.mall_id' => \Yii::$app->mall->id,
                'l.is_delete' => 0
            ]);
        if ($user) {
            $query->andWhere(['l.user_id' => $user->id]);
        }
        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);
        if ($page) {
            /**
             * @var BasePagination $pagination
             */
            $query = $query->page($pagination, 20, $page);
        }
        $list = $query->select('l.*')->orderBy('l.created_at DESC')->asArray()->all();
        foreach ($list as &$item) {
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;
        return $returnData;
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-08-04
     * @Time: 16:47
     * @Note:团队奖励
     * @param $user
     * @param $params
     * @return mixed
     */
    public function getTeamPriceList($user, $params)
    {
        $page = isset($params["page"]) ? $params["page"] : 1;//分销层级
        $query = TeamPriceLog::find()
            ->alias('l')
            ->where([
                'l.mall_id' => \Yii::$app->mall->id,
                'l.is_delete' => 0
            ]);
        if ($user) {
            $query->andWhere(['l.user_id' => $user->id]);
        }
        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);
        if ($page) {
            /**
             * @var BasePagination $pagination
             */
            $query = $query->page($pagination, 20, $page);
        }
        $list = $query->select('l.*')->orderBy('l.created_at DESC')->asArray()->all();
        foreach ($list as &$item) {
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;
        return $returnData;
    }

    /**
     * 获取分销商复购奖励记录列表
     * @param $user
     * @param $params
     * @return array
     */
    public function getSubsidyPriceList($user, $params)
    {
        $page = isset($params["page"]) ? $params["page"] : 1;//分销层级
        $query = SubsidyPriceLog::find()
            ->alias('l')
            ->where([
                'l.mall_id' => \Yii::$app->mall->id,
                'l.is_delete' => 0
            ]);
        if ($user) {
            $query->andWhere(['l.user_id' => $user->id]);
        }
        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);
        if ($page) {
            /**
             * @var BasePagination $pagination
             */
            $query = $query->page($pagination, 20, $page);
        }
        $list = $query->select('l.*')->orderBy('l.created_at DESC')->asArray()->all();
        foreach ($list as &$item) {
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
        }
        unset($item);
        $returnData['list'] = $list;
        $returnData["pagination"] = $pagination;
        return $returnData;
    }


    /**
     * 获取分销订单列表
     * @param $order
     * @return array
     */
    public function getDistributionOrderList($order)
    {
        $order_id = $order["id"];
        $query = DistributionOrder::find()->where(["mall_id" => \Yii::$app->mall->id, "order_id" => $order_id]);
        $query = $query->orderBy("id desc");
        $list = $query->with("order")->all();
        return $list;
    }
}
