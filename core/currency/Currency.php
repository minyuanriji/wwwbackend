<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 金额变动通用类
 * Author: zal
 * Date: 2020-04-15
 * Time: 17:45
 */

namespace app\core\currency;

use app\models\Mall;
use app\models\User;
use yii\base\Component;
use yii\db\Exception;

/**
 * @property BalanceModel $balance;
 * @property ScoreModel $score;
 * @property BrokerageModel $brokerage;
 * @property User $user;
 */
class Currency extends Component
{
    private $score;
    private $balance;
    private $user;
    private $brokerage;
    private $income;

    /**
     * @return BalanceModel
     * @throws Exception
     */
    public function getBalance()
    {
        $form = new BalanceModel();
        $form->user = $this->getUser();
        $form->mall = \Yii::$app->mall;
        return $form;
    }

    /**
     * @return ScoreModel
     * @throws Exception
     */
    public function getScore()
    {
        $form = new ScoreModel();
        $form->user = $this->getUser();
        $form->mall = \Yii::$app->mall;
        return $form;
    }

    /**
     * @param $user
     * @return $this
     * @throws Exception
     */
    public function setUser($user)
    {
        if ($user instanceof User) {
            $this->user = $user;
        } else {
            throw new Exception('用户不存在');
        }
        return $this;
    }

    /**
     * @return User
     * @throws Exception
     */
    public function getUser()
    {
        if ($this->user instanceof User) {
            return $this->user;
        } else {
            throw new Exception('用户不存在');
        }
    }

    /**
     * @return BrokerageModel
     * @throws Exception
     */
    public function getBrokerage()
    {
        $form = new BrokerageModel();
        $form->user = $this->getUser();
        $form->mall = \Yii::$app->mall;
        /* @var Share $share */
        $share = $this->user->share;
        if (!$share) {
            throw new Exception('指定用户不是分销商');
        }
        $form->share = $share;
        return $form;
    }

    /**
     * 收益
     * @return BalanceModel
     * @throws Exception
     */
    public function getIncome()
    {
        $form = new IncomeModel();
        $form->user = $this->getUser();
        $form->mall = \Yii::$app->mall;
        return $form;
    }
}
