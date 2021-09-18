<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 余额model
 * Author: zal
 * Date: 2020-04-15
 * Time: 17:45
 */

namespace app\core\currency;


use app\events\BalanceEvent;
use app\forms\common\template\TemplateSend;
use app\forms\common\template\tplmsg\AccountChange;
use app\forms\common\template\tplmsg\Tplmsg;
use app\models\BalanceLog;
use app\models\BaseModel;
use app\models\Mall;
use app\models\User;
use yii\db\Exception;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class BalanceModel extends BaseModel implements BaseCurrency
{
    public $mall;
    public $user;

    /**
     * 增加余额
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    public function add($price, $desc, $customDesc = '', $source_type = '', $source_id = 0)
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        /* @var User $user */
        $userInfo = $this->user;
        $t = \Yii::$app->db->beginTransaction();
        $userInfo->balance += $price;
        $userInfo->total_balance += $price;
        if ($userInfo->save()) {
            try {
                $this->createLog(1, $price, $desc, $customDesc, $userInfo->balance, $source_type, $source_id);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($userInfo), $userInfo->errors, 1);
        }
    }

    /**
     * 减余额
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @param string $source_type
     * @param  $source_id
     * @return bool
     * @throws Exception
     */
    public function sub($price, $desc, $customDesc = '', $source_type = '', $source_id = 0)
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        if ($this->user->balance < $price) {
            throw new Exception('用户余额不足');
        }
        /* @var User $user */
        $user = $this->user;
        $t = \Yii::$app->db->beginTransaction();
        $user->balance -= $price;
        if ($user->save()) {
            try {
                $this->createLog(2, $price, $desc, $customDesc, $user->balance, $source_type, $source_id);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($user), $user->errors, 1);
        }
    }

    /**
     * 余额查询
     * @return mixed
     */
    public function select()
    {
        return round($this->user->balance, 2);
    }

    /**
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    public function refund($price, $desc, $customDesc = '', $source_type = '', $source_id = 0)
    {
        $this->mall = \Yii::$app->mall;
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('金额必须为数字类型');
        }
        /* @var User $userInfo */
        $userInfo = $this->user;
        $t = \Yii::$app->db->beginTransaction();
        $userInfo->balance += $price;
        if ($userInfo->save()) {
            try {
                $this->createLog(1, $price, $desc, $customDesc, $userInfo->balance, $source_type, $source_id);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($userInfo), $userInfo->errors, 1);
        }
    }

    /**
     * 记录日志
     * @param $type
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @param $balance
     * @return bool
     * @throws \Exception
     */
    private function createLog($type, $price, $desc, $customDesc, $balance = 0, $source_type = '', $source_id = 0)
    {
        if ($price == 0) {
            \Yii::warning('余额为' . $price . '不记录日志');
            return true;
        }
        if (!$customDesc) {
            $customDesc = \Yii::$app->serializer->encode(['msg' => '用户余额变动说明']);
        }
        $form = new BalanceLog();
        $form->mall_id = $this->user->mall_id;
        $form->user_id = $this->user->id;
        $form->type = $type;
        $form->money = $price;
        $form->desc = $desc;
        $form->custom_desc = $customDesc;
        $form->balance = $balance == 0 ? $this->user->balance : $balance;
        if ($source_type) {
            $form->source_type = $source_type;
        }
        if ($source_id) {
            $form->source_id = $source_id;
        }
        if ($form->save()) {
            //触发余额变动事件
            $event = new BalanceEvent();
            $event->balance_log = $form;
            \Yii::$app->trigger(BalanceLog::EVENT_BALANCE_CHANGE, $event);
            return true;
        } else {
            throw new \Exception($this->responseErrorMsg($form), $form->errors, 1);
        }
    }
}
