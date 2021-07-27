<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销插件-处理经销分佣公共类
 * Author: zal
 * Date: 2020-05-29
 * Time: 18:10
 */

namespace app\plugins\agent\forms\common;

use app\core\BasePagination;
use app\logic\UserLogic;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\MallSetting;
use app\models\PriceLog;
use app\models\User;
use app\models\UserParent;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentLevel;
use app\plugins\agent\models\AgentOrder;
use app\plugins\agent\models\AgentPriceLogType;
use app\plugins\agent\models\AgentSetting;
use app\plugins\agent\Plugin;
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
     * @return AgentSetting|null
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = AgentSetting::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new AgentSetting();
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
    public function getAgentInfo($user)
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
        $agent = $this->getAgentUser($user);
        $level_name = '普通用户';
        $is_price = '0.00';
        $returnData['nickname'] = $user->nickname;
        $returnData['avatar_url'] = $user->avatar_url;
        $returnData['user_id'] = $user->id;
        $returnData["is_parent"] = 0;
        $returnData['is_agent'] = 0;
        $returnData["level_name"] = $level_name;
        if (empty($agent)) {
            return $returnData;
        }
        if (!empty($agent)) {
            $returnData['is_agent'] = 1;
            $total_price = $agent->total_price;
            $agentLevel = AgentLevel::findOne(['level' => $agent->level, 'mall_id' => $user->mall_id]);
            $level_name = $agentLevel ? $agentLevel->name : "普通用户";
            $frozen_price = floatval($agent->frozen_price);
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
        $user_parent = UserParent::find()
            ->alias('uc')
            ->where(['uc.user_id' => \Yii::$app->user->identity->id, 'uc.is_delete' => 0])
            ->leftJoin(['a' => Agent::tableName()], 'a.user_id=uc.parent_id')
            ->andWhere(['a.is_delete' => 0])
            ->orderBy('uc.level ASC')->asArray()->one();
        if ($user_parent) {
            $returnData['is_parent'] = 1;
            $user = User::findOne($user_parent['parent_id']);
            if ($user) {
                $parent_agent['nickname'] = $user->nickname;
                $parent_agent['avatar_url'] = $user->avatar_url;
                $agent = Agent::findOne(['user_id' => $user->id, 'is_delete' => 0]);
                if ($agent) {
                    $level = AgentLevel::findOne(['level' => $agent->level, 'mall_id' => \Yii::$app->mall->id]);
                    if (!$level) {
                        $parent_agent['level_name'] = '普通经销商';
                    } else {
                        $parent_agent['level_name'] = $level->name;
                    }
                }
                $parent_agent['user_id'] = $user->id;
                $returnData['parent_agent'] = $parent_agent;
            }
        }
        $returnData["total_price"] = floatval($total_price);
        $returnData["level_name"] = $level_name;
        $returnData["frozen_price"] = $frozen_price ?? '0.00';
        $returnData["is_price"] = $is_price ?? '0.00';
        $returnData["yesterday_price"] = $yesterday_price ?? '0.00';
        return $returnData;
    }

    /**
     * 获取经销用户信息
     * @param $user
     * @return Agent|null
     */
    public function getAgentUser($user)
    {
        if (empty($user)) {
            return null;
        }
        $agent = Agent::findOne(['user_id' => $user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$agent) {
            return null;
        }
        return $agent;
    }

    /**
     * 获取经销记录列表
     * @param $user
     * @param $params
     * @return array
     */
    public function getAgentLogList($user, $params)
    {
        $plugin = new Plugin();
        $sign = $plugin->getName();
        if (!$sign) {
            $this->sign = 'mall';
        } else {
            $this->sign = $sign;
        }

        $is_price = isset($params['is_price']) ? $params['is_price'] : -1;//分佣状态
        $page = isset($params["page"]) ? $params["page"] : 1;//经销层级
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

        $query = $query
            ->orderBy(['l.created_at' => SORT_ASC]);

        if ($page) {
            /**
             * @var BasePagination $pagination
             */
            $query = $query->page($pagination, 20, $page);
        }
        $list = $query->select('l.*,u.avatar_url,u.nickname,l.child_id as user_id')->orderBy('l.created_at DESC')->asArray()->all();
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
            $price_type = AgentPriceLogType::findOne(['price_log_id' => $item['id']]);
            if ($price_type) {
                $price_type_list = AgentPriceLogType::PRICE_TYPE;
                $item['price_type'] = $price_type_list[$price_type->type];
            } else {
                $item['price_type'] = '未知奖项';

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
    public function getAgentOrderList($order)
    {
        $order_id = $order["id"];
        $query = AgentOrder::find()->where(["mall_id" => \Yii::$app->mall->id, "order_id" => $order_id]);
        $query = $query->orderBy("id desc");
        $list = $query->with("order")->all();
        return $list;
    }

    /**
     * 获取用户索引下级中所有经销商等级对应的人数
     * @return array
     */
    public static function getAllLevelTotal(){
        $userIds = UserLogic::getAllChildIdsList("id",\Yii::$app->user->id,\Yii::$app->mall->id);
        $list = Agent::find()->where(["user_id" => $userIds,"is_delete" => Agent::IS_DELETE_NO])->select(["count(id) as total","level"])->
                        groupBy("level")->orderBy("level asc")->asArray()->all();
        $data =[];
        if(!empty($list)){
            foreach ($list as $value){
                $data[$value["level"]] = $value;
            }
        }
        $levelList = AgentLevel::find()->where(["is_delete" => AgentLevel::IS_DELETE_NO])->select(["name","level"])->orderBy("level asc")->asArray()->all();
        $returnData = [];
        if(!empty($levelList)){
            foreach ($levelList as $item){
                $item["level"] = intval($item["level"]);
                $item["total"] = isset($data[$item["level"]]["total"]) ? intval($data[$item["level"]]["total"]) : 0;
                $returnData[] = $item;
            }
        }
        return $returnData;
    }
}
