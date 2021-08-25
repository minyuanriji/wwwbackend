<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 接口-用户收益model类
 * Author: zal
 * Date: 2020-05-29
 * Time: 11:16
 */

namespace app\forms\api\user;

use app\core\ApiCode;
use app\core\BasePagination;
use app\logic\OptionLogic;
use app\models\BaseModel;
use app\models\IncomeLog;
use app\models\MemberLevel;
use app\models\Option;
use app\models\User;
use app\plugins\distribution\Plugin;

use app\models\PriceLog;
use app\models\Order;

class UserIncomeForm extends BaseModel
{
    public $page;
    public $limit;
    //插件标识
    public $sign;
    //1直推2间推
    public $flag;

    public $status;

    public $source_type;

    public $updated_at;

    public function rules()
    {
        return [
            [['page', 'limit', 'flag'], 'integer'],
            [['sign', 'source_type','updated_at'], 'string'],
            [['status'], 'integer'],
            ['page', 'default', 'value' => 1],
            ['limit', 'default', 'value' => 20],
        ];
    }


    /**
     * 计算可提现金额
     * @return float
     */
    public function getIncomeTotal()
    {
        $income = PriceLog::find()->alias("pl")
            ->leftJoin(["o" => Order::tableName()], "o.id=pl.order_id")
            ->andWhere(["o.is_pay" => 1]) //订单为已支付
            ->andWhere(["o.mall_id" => \Yii::$app->mall->id])
            ->andWhere(["pl.user_id" => \Yii::$app->user->id])
            ->andWhere(["pl.is_price" => 1, "pl.status" => 1])  //已发放，有效记录
            ->sum("pl.price");
        return $income ? $income : 0;
    }

    /**
     * 计算未结算金额
     * @return  float
     */
    public function getIncomeFrozenTotal()
    {
        $incomeFrozen = PriceLog::find()->alias("pl")
            ->leftJoin(["o" => Order::tableName()], "o.id=pl.order_id")
            ->andWhere(["o.is_pay" => 1]) //订单为已支付
            ->andWhere(["o.mall_id" => \Yii::$app->mall->id])
            ->andWhere(["pl.user_id" => \Yii::$app->user->id])
            ->andWhere(["pl.is_price" => 0, "pl.status" => 1])  //未发放，有效记录
            ->sum("pl.price");
        return $incomeFrozen ? $incomeFrozen : 0;
    }

    /**
     * 用户收益信息
     * @Author: zal
     * @Date: 2020-04-28
     * @Time: 17:33
     * @return array
     */
    public function getIncomeInfo()
    {
        if (\Yii::$app->user->isGuest) {
            return null;
        }
        $user_id = \Yii::$app->user->id;

        /** @var User $userInfo */
        $userInfo = User::getOneData($user_id);
        $result = [
            //可提现
            'income' => round($userInfo->income, 2),

            //待收益
            'income_frozen' => round(str_replace('-','',$userInfo -> income_frozen), 2),

            'total_income' => round($userInfo->total_income, 2),

            'total_cash' => 0,
            'yesterday_income' => $this->getYesterdayIncome()
        ];
        $result['total_income'] = round($result['income'] + $result['income_frozen'], 2);

        $result = array_merge($result, \Yii::$app->plugin->getUserInfo($userInfo));
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', $result);
    }


    /**
     * 昨日收益
     * @return float
     */
    private function getYesterdayIncome()
    {
        $yesterday = date("Y-m-d", strtotime("-1 day"));
        $begin_time = strtotime($yesterday . " 00:00:00");
        $end_time = strtotime($yesterday . " 23:59:59");
        $query = IncomeLog::find()->where(["mall_id" => \Yii::$app->mall->id, "user_id" => \Yii::$app->user->id, "type" => IncomeLog::TYPE_IN]);
        $query->andWhere(['between', "created_at", $begin_time, $end_time]);
        $incomeMoney = $query->sum("income");
        return floatval($incomeMoney);
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-01
     * @Time: 16:49
     * @Note:收益记录
     * @return array
     */
    public function getList()
    {
        $query = IncomeLog::find()->where(['user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0]);
        /*if ($this->status == 0) {
            $query->andWhere(['type' => IncomeLog::TYPE_IN]);
        }
        if ($this->status == 1) {
            $query->andWhere(['type' => IncomeLog::TYPE_OUT]);
        }*/

        if ($this->updated_at) {
            $query->andWhere('FROM_UNIXTIME(updated_at,"%Y年%m月")="'.$this->updated_at.'"');
        }

        if ($this->source_type) {
            $query->andWhere(['source_type' => $this->source_type]);
        }
        $incomeQuery = clone $query;
        $income = $incomeQuery->andWhere(['type' => 1])->sum('income');

        $expenditureQuery = clone $query;
        $expenditure = $expenditureQuery->andWhere(['type' => 2])->sum('income');
        /**
         * @var BasePagination $pagination
         */
        $list = $query->page($pagination, 10, $this->page)->orderBy('created_at DESC')->asArray()->all();

        foreach ($list as &$item) {
            $item['created_at'] = date('m月d日 H:i', $item['created_at']);
            $item['money'] = sprintf("%.2f",$item['money']);
            $item['income'] = sprintf("%.2f",$item['income']);
        }

        return $this->returnApiResultData(
            ApiCode::CODE_SUCCESS,
            null,
            [
                'list'              => $list,
                'detailed_count'    => [
                    'income'        => $income,
                    'expenditure'   => $expenditure,
                ],
                'pagination'        => $pagination
            ]
        );
    }


    /**
     * 获取用户分销数据
     * @return array
     * @throws \Exception
     */
    public function getUserDistributionInfo()
    {
        $user_id = \Yii::$app->user->id;
        /** @var User $userInfo */
        $userInfo = User::getOneData($user_id);
        if (empty($this->sign)) {
            $this->sign = "area";
        }
        /** @var Plugin $plugin */
        $plugin = \Yii::$app->plugin->getPlugin($this->sign);
        $list = $plugin->getList($userInfo, $this->attributes);

        $parentData = [];
        $username = "平台";
        $levelName = "平台";
        $settings = OptionLogic::get(Option::NAME_IND_SETTING);
        $avatar_url = isset($settings["logo"]) ? $settings["logo"] : "";
        $mobile = "暂无";
        $parent_id = 0;
        if (isset($userInfo->parent)) {
            $parent = $userInfo->parent;
            $level = $parent->level;
            if ($level > 0) {
                $memberLevels = MemberLevel::getOneData(["level" => $level]);
                $levelName = $memberLevels->name;
            }
            $username = !empty($parent->nickname) ? $parent->nickname : $parent->username;
            $mobile = $parent->mobile;
            $avatar_url = empty($parent->avatar_url) ? $avatar_url : $parent->avatar_url;
            $parent_id = $parent->id;
        }
        $parentData["parent"]["parent_id"] = $parent_id;
        $parentData["parent"]["username"] = $username;
        $parentData["parent"]["mobile"] = $mobile;
        $parentData["parent"]["avatar_url"] = $avatar_url;
        $parentData["parent"]["level_name"] = $levelName;
        $list = array_merge($list, $parentData);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', $list);
    }
}
