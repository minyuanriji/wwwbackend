<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 经销插件-处理经销分佣公共类
 * Author: zal
 * Date: 2020-05-29
 * Time: 18:10
 */

namespace app\plugins\boss\forms\common;

use app\core\BasePagination;
use app\models\CommonOrderDetail;
use app\models\Goods;
use app\models\MallSetting;
use app\models\PriceLog;
use app\models\User;
use app\models\UserChildren;
use app\models\UserParent;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;
use app\plugins\boss\models\BossOrder;
use app\plugins\boss\models\BossPriceLog;
use app\plugins\boss\models\BossPriceLogType;
use app\plugins\boss\models\BossSetting;
use app\plugins\boss\Plugin;
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
     * @return BossSetting|null
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = BossSetting::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new BossSetting();
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
    public function getBossInfo(User $user)
    {
        if (!$user) {
            return null;
        }
        $returnData = [];
        $yesterday_price = 0;
        $boss = $this->getBossUser($user);
        $compute_type = BossSetting::getValueByKey(BossSetting::COMPUTE_TYPE, $this->mall->id);
        $returnData["is_boss"] = 1;
        if (!$boss) {
            $returnData["is_boss"] = 0;
        }
        $level_name = '普通用户';
        $yesterday_total_price=0;
        $returnData["yesterday_total_price"]=0;
        $income_rate=0;

        if($user){
            $returnData['nickname'] = $user->nickname;
            $returnData['avatar_url'] =$user->avatar_url;
            $returnData['mobile'] = $user->mobile;
            $returnData['user_id'] = $user->id;
        }
        $returnData["is_boss"] = 0;

        if (!empty($boss)) {
            $returnData["is_boss"] = 1;
            $total_price = $boss->total_price;
            $bossLevel = BossLevel::findOne(['level' => $boss->level, 'is_delete' => 0, 'is_enable' => 1]);
            $level_name = '普通用户';
            if (!empty($bossLevel)) {
                $level_name = $bossLevel->name;
                $income_rate = $bossLevel->price;
            }
            $start_time = strtotime(date("Y-m-d 00:00:00", strtotime("-1 day")));
            $end_time = $start_time + 1 * 24 * 60 * 60;
            if ($compute_type == 0) {
                $total_price = CommonOrderDetail::find()->where(['status' => 1, 'mall_id' => $this->mall->id, 'is_delete' => 0])
                    ->andWhere(['>=', 'updated_at', $start_time])
                    ->andWhere(['<', 'updated_at', $end_time])
                    ->sum('price');
            }
            if ($compute_type == 0) {
                $total_price = CommonOrderDetail::find()->where(['status' => 1, 'mall_id' => $this->mall->id, 'is_delete' => 0])
                    ->andWhere(['>=', 'updated_at', $start_time])
                    ->andWhere(['<', 'updated_at', $end_time])
                    ->sum('profit');
            }
            $yesterday_total_price = $total_price;
            $total_price = BossPriceLog::find()->where(['user_id' => $boss->user_id, 'mall_id' => $this->mall->id, 'is_delete' => 0])
                ->andWhere(['>=', 'updated_at', $start_time])
                ->andWhere(['<', 'updated_at', $end_time])
                ->sum('price');
            $yesterday_price = $total_price;
        }
        $parent = User::findOne($user->parent_id);
        if ($parent) {
            $returnData["parent_nickname"] = $parent->nickname;
            $returnData["parent_avatar"] = $parent->avatar_url;
            $returnData["parent_mobile"] = $parent->mobile;
            $returnData["parent_id"] = $parent->id;
            $returnData["is_parent"] = 1;
        } else {
            $returnData["is_parent"] = 0;
        }
        $level_list = BossLevel::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'is_enable' => 1])->asArray()->all();
        foreach ($level_list as &$level) {
            $user_count=UserChildren::find()->alias('uc')
                ->leftJoin(['sa'=>Boss::tableName()],'sa.user_id=uc.child_id')
                ->andWhere(['sa.level'=>$level['level'],'sa.is_delete'=>0,'uc.is_delete'=>0,'uc.user_id'=>$user->id])
                ->count();
            $level['count']=$user_count??0;
        }
        $returnData["level_list"] = $level_list;
        $returnData["yesterday_total_price"] = $yesterday_total_price;
        $returnData["level_name"] = $level_name;
        $returnData["income_rate"] = $income_rate;
        $returnData["yesterday_price"] = $yesterday_price ?? '0.00';
        $returnData["user_id"] = \Yii::$app->user->identity->id;
        $returnData['nickname'] = $user->nickname;
        $returnData['avatar_url'] = $user->avatar_url;
        return $returnData;
    }

    /**
     * 获取经销用户信息
     * @param $user
     * @return Boss|null
     */
    public function getBossUser($user)
    {
        if (empty($user)) {
            return null;
        }
        $boss = Boss::findOne(['user_id' => $user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$boss) {
            return null;
        }
        return $boss;
    }

    /**
     * 获取经销记录列表
     * @param $user
     * @param $params
     * @return array
     */
    public function getBossLogList($user, $params)
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
            $price_type = BossPriceLogType::findOne(['price_log_id' => $item['id']]);
            if ($price_type) {
                $price_type_list = BossPriceLogType::PRICE_TYPE;
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
    public function getBossOrderList($order)
    {
        $order_id = $order["id"];
        $query = BossOrder::find()->where(["mall_id" => \Yii::$app->mall->id, "order_id" => $order_id]);
        $query = $query->orderBy("id desc");
        $list = $query->with("order")->all();
        return $list;
    }
}
