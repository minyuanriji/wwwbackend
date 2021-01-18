<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商佣金变动通用类
 * Author: zal
 * Date: 2020-04-15
 * Time: 17:45
 */

namespace app\core\currency;

use app\forms\common\Distribution\DistributionLevelCommon;
use app\forms\common\template\tplmsg\AccountChange;
use app\models\BaseModel;
use app\models\Mall;
use app\models\User;
use yii\db\Exception;

/**
 * @property Mall $mall;
 * @property User $user;
 * @property Share $share;
 */
class BrokerageModel extends BaseModel implements BaseCurrency
{
    public $mall;
    public $user;
    public $share;

    /**
     * @param float $price
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    public function add($price, $desc, $customDesc = '分销商佣金变动说明')
    {
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('佣金必须为数字类型');
        }
        $t = \Yii::$app->db->beginTransaction();
        $this->share->money += $price;
        $this->share->total_money += $price;
        if ($this->share->save()) {
            $commonShareLevel = DistributionLevelCommon::getInstance();
            // 累计佣金触发分销等级改变
            $commonShareLevel->userId = $this->share->user_id;
            $commonShareLevel->levelShare(DistributionLevelCommon::TOTAL_MONEY);
            try {
                $this->createLog(1, $price, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($this->share), $this->share->errors, 1);
        }
    }

    /**
     * @param float $price
     * @param $desc
     * @param $customDesc
     * @return bool
     * @throws Exception
     */
    public function sub($price, $desc, $customDesc = "分销商佣金变动说明")
    {
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('佣金必须为数字类型');
        }
        if ($this->share->money < $price) {
            throw new Exception('分销商可用佣金不足');
        }
        $t = \Yii::$app->db->beginTransaction();
        $this->share->money -= $price;
        if ($this->share->save()) {
            try {
                $this->createLog(2, $price, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($this->share), $this->share->errors, 1);
        }
    }

    public function select()
    {
        return round($this->share->money, 2);
    }

    public function selectTotal()
    {
        return round($this->share->total_money, 2);
    }

    /**
     * @param float $price
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    public function refund($price, $desc, $customDesc = '分销商佣金变动说明')
    {
        if (!is_float($price) && !is_int($price) && !is_double($price)) {
            throw new Exception('佣金必须为数字类型');
        }
        $t = \Yii::$app->db->beginTransaction();
        $this->share->money += $price;
        if ($this->share->save()) {
            try {
                $this->createLog(1, $price, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($this->share), $this->share->errors, 1);
        }
    }

    /**
     * @param $type
     * @param $price
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    private function createLog($type, $price, $desc, $customDesc = '分销商佣金变动说明')
    {
        if ($price == 0) {
            \Yii::warning('佣金为' . $price . '不记录日志');
            return true;
        }
        \Yii::warning($customDesc);
        $form = new ShareCashLog();
        $form->user_id = $this->user->id;
        $form->mall_id = $this->mall->id;
        $form->type = $type;
        $form->price = $price;
        $form->desc = $desc;
        $form->custom_desc = $customDesc;
        if ($form->save()) {
            $templateSend = new AccountChange([
                'remark' => $customDesc,
                'desc' => $desc,
                'page' => 'pages/user/index',
                'user' => $this->user
            ]);
            $templateSend->send();
            return true;
        } else {
            throw new Exception($this->responseErrorMsg($form), $form->errors, 1);
        }
    }
}
