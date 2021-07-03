<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 发放积分和余额
 * Author: xuyaoxiang
 * Date: 2020/9/24
 * Time: 20:14
 */

namespace app\plugins\group_buy\services;

use app\models\Mall;
use app\models\User;
use app\plugins\group_buy\models\Order;
use app\plugins\group_buy\models\PluginGroupBuyActiveItem;
use app\plugins\group_buy\models\PluginGroupBuyActive;
use app\models\ScoreLog;
use app\plugins\group_buy\models\PluginGroupBuyGoods;

class GiveScoreServices
{
    use ReturnData;

    public $mall_id;
    public $user_id;
    private $active_item;
    private $group_buy_goods;

    public function __construct($mall_id)
    {
        $this->mall_id = $mall_id;
    }

    public function send($order_id)
    {
        $return = $this->aboveConditions($order_id);
        if ($return['code'] > 0) {
            return $this->returnApiResultData($return['code'], $return['msg']);
        }

        $creator = $this->getCreatorItem($this->active_item->active_id);

        if ($this->checkScore()) {
            $this->giveOutScore($this->group_buy_goods->send_score, $creator->user_id, $this->mall_id);
        }

        if ($this->checkBalance()) {
            $this->giveOutMoney($this->group_buy_goods->send_balance, $creator->user_id, $this->mall_id);
        }

        $model = PluginGroupBuyActive::findOne($this->active_item->active_id);

        $model->is_send = 1;

        if (!$model->save()) {
            return $this->returnApiResultData(98, "active保存失败");
        }

        return $this->returnApiResultData(0, "发放成功");
    }

    public function getCreatorItem($active_id)
    {
        return PluginGroupBuyActiveItem::find()
            ->where(['active_id' => $active_id, 'mall_id' => $this->mall_id, 'is_creator' => 1])
            ->one();
    }

    private function getGroupBuyAward($id)
    {
        $this->group_buy_goods = PluginGroupBuyGoods::findOne($id);
        return $this->group_buy_goods;
    }

    public function aboveConditions($order_id)
    {
        $this->active_item = $this->getActiveItemByorder($order_id);

        $this->group_buy_goods = $this->getGroupBuyAward($this->active_item->active->group_buy_id);

        //积分金额都为0
        if (!$this->checkScore() and !$this->checkBalance()) {
            return $this->returnApiResultData(10,"没有设置奖励");
        }

        //是否已经发放过
        if ($this->active_item->active->is_send == 1) {
            return $this->returnApiResultData(11,"奖励已经发放了");
        }

        $count = $this->activeConfirmOrderCount($this->active_item->id);

        if ($count < $this->active_item->active->people) {
            return $this->returnApiResultData(12,"收货订单数还没有达到成团人数");
        }

        return $this->returnApiResultData(0,"条件达到");
    }

    private function checkScore()
    {
        if ($this->group_buy_goods->send_score == 0) {
            return false;
        }
        return true;
    }

    private function checkBalance()
    {
        if ($this->group_buy_goods->send_balance == 0) {
            return false;
        }
        return true;
    }

    public function getActiveItemByorder($order_id)
    {
        return $this->active_item = PluginGroupBuyActiveItem::find()
            ->with('active')
            ->where(['order_id' => $order_id, 'mall_id' => $this->mall_id])
            ->one();
    }

    /**
     * 收货订单数
     * @param $active_id
     * @return bool|int|string|null
     */
    public function activeConfirmOrderCount($active_id)
    {
        return $count = Order::find()
            ->alias('o')
            ->rightJoin(['ai' => PluginGroupBuyActiveItem::tableName()], 'ai.order_id=o.id')
            ->rightJoin(['a' => PluginGroupBuyActive::tableName()], 'ai.active_id=a.id')
            ->where(['o.mall_id' => $this->mall_id])
            ->where(['a.id' => $active_id])
            ->where(['o.deleted_at' => 0])
            ->where(['o.status' => [Order::STATUS_WAIT_COMMENT, Order::STATUS_COMPLETE], 'o.sign' => 'group_buy'])
            ->count();
    }

    /**
     * 积分操作
     * @param $score
     * @param $user_id
     */
    public function giveOutScore($score, $user_id, $mall_id)
    {
        $user = User::findOne($user_id);
        $mall = Mall::findOne($mall_id);

        \Yii::$app->setMall($mall);

        \Yii::$app->currency->setUser($user)->score->add($score, "拼团积分发放,增加" . $score . "积分");
    }

    /**
     * 余额操作
     * @param $money
     * @param $user_id
     */
    public function giveOutMoney($money, $user_id, $mall_id)
    {
        $user = User::findOne($user_id);
        $mall = Mall::findOne($mall_id);

        \Yii::$app->setMall($mall);

        \Yii::$app->currency->setUser($user)->balance->add($money, "拼团余额发放,增加" . $money . "余额");
    }
}