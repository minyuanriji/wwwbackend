<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 收益model
 * Author: zal
 * Date: 2020-05-28
 * Time: 16:45
 */

namespace app\core\currency;

use app\events\BalanceEvent;
use app\events\IncomeEvent;
use app\helpers\SerializeHelper;
use app\logic\OptionLogic;
use app\models\Option;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\ErrorLog;
use app\models\IncomeLog;
use app\models\Mall;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use yii\db\Exception;

use app\forms\api\user\UserIncomeForm;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class IncomeModel extends BaseModel implements BaseCurrency
{
    public $mall;
    public $user;

    /**
     * 增加收益
     * @param $price
     * @param $desc
     * @param $order_detail_id
     * @param int $flag 0冻结金额 1过售后期，增加收益 2退款
     * @param int $from 来源1分销
     * @return bool
     * @throws Exception
     */
    public function add($price, $desc, $order_detail_id = 0, $flag = 0,$from = 1)
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->createLog(1, $price, $desc,$order_detail_id, $flag, 0,$from);
            $t->commit();
            return true;
        } catch (Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * 减余额
     * @param $price
     * @param $desc
     * @param int $flag 0冻结金额1过售后期，增加收益
     * @param $order_detail_id
     * @return bool
     * @throws Exception
     */
    public function sub($price, $desc, $order_detail_id = 0, $flag = 0)
    {
        $this->mall = \Yii::$app->mall;
        $payment = OptionLogic::get(Option::NAME_PAYMENT, \Yii::$app->mall->id, Option::GROUP_APP);
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        if ($this->user->income < $price) {
            throw new Exception('用户收益不足');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->createLog(2, $price, $desc,$order_detail_id, $flag,0);
            $t->commit();
            return true;
        } catch (Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * 收益查询
     * @return mixed
     */
    public function select()
    {
        $form = new UserIncomeForm();
        return round($this->getIncomeTotal(), 2);
    }

    /**
     * 退款
     * @param $price
     * @param $desc
     * @param $order_detail_id
     * @param int $flag 0冻结金额 1过售后期，增加收益 2退款
     * @return bool
     * @throws Exception
     */
    public function refund($price, $desc,$order_detail_id = 0, $flag = 0)
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $this->createLog(1, $price, $desc,$order_detail_id, $flag, 0);
            $t->commit();
            return true;
        } catch (Exception $e) {
            $t->rollBack();
            throw $e;
        }
    }

    /**
     * 记录日志
     * @param $type
     * @param $price
     * @param $desc
     * @param $order_detail_id
     * @param int $flag 0冻结金额1过售后期，增加收益2退款3提现
     * @param int $from 来源1分销
     * @param $income
     * @return bool
     * @throws \Exception
     */
    private function createLog($type, $price, $desc,$order_detail_id = 0, $flag = 0,$income = 0,$from = 1)
    {
        if ($price == 0) {
            \Yii::warning('余额为' . $price . '不记录日志');
            return true;
        }

        $distribution = Distribution::findOne(['user_id' => $this->user->id]);

        if($flag == 1 || $flag == 2){
            //查找是否已有冻结记录，有的话更新冻结状态
            $form = IncomeLog::findOne(["mall_id" => $this->user->mall_id,"user_id"=>$this->user->id,
                "order_detail_id" => $order_detail_id,"type" => IncomeLog::TYPE_IN,
                "from" => $from
            ]);
            if(!empty($form)){
                $form->flag = $flag;
            }else{
                $form = new IncomeLog();
                $form->mall_id = $this->user->mall_id;
                $form->user_id = $this->user->id;
                $form->order_detail_id = $order_detail_id;
                $form->type = $type;
                $form->money = $price;
                $form->desc = $desc;
                $form->flag = $flag;
                $form->from = $from;
                $form->income = $distribution->total_price;
            }
        }else{
            //查找是否已有冻结记录，有的话直接返回
            $form = IncomeLog::findOne(["mall_id" => $this->user->mall_id,"user_id" => $this->user->id,
                "order_detail_id" => $order_detail_id,"type" => $type, "from" => $from
            ]);
            if(!empty($form)){
                return true;
            }
            $form = new IncomeLog();
            $form->mall_id = $this->user->mall_id;
            $form->user_id = $this->user->id;
            $form->order_detail_id = $order_detail_id;
            $form->type = $type;
            $form->money = $price;
            $form->desc = $desc;
            $form->flag = $flag;
            $form->from = $from;
            $form->income = $distribution->total_price;
        }

        if ($form->save()) {
            //触发收益变动事件
            $event             = new IncomeEvent();
            $event->income_log = $form;
            \Yii::$app->trigger(IncomeLog::EVENT_INCOME_CHANGE, $event);

            return true;
        } else {
            throw new \Exception($this->responseErrorMsg($form), $form->errors, 1);
        }
    }
}
