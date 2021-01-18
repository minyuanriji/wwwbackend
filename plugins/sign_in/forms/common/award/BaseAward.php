<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理签到奖励公共基础类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common\award;

use app\models\BaseModel;
use app\models\User;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\models\SignIn;

/**
 * @property User $user
 * @property Common $common
 */
abstract class BaseAward extends BaseModel
{
    public $user;
    public $common;
    public $day;
    public $status;
    public $token;

    /**
     * 校验
     * @return mixed
     * @throws \Exception
     *
     */
    abstract public function check();

    /**
     * 添加签到奖励
     * @throws \Exception
     * @return SignIn
     */
    public function addSignIn()
    {
        $res = $this->check();
        if (!$res) {
            throw new \Exception('校验不通过x01');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $common = $this->common;
            $award = $common->getAwardByDay($this->status, $this->day);
            if (!$award) {
                throw new \Exception('错误的奖励信息');
            }
            $this->otherSave();
            $form = new SignIn();
            $form->mall_id = \Yii::$app->mall->id;
            $form->user_id = $this->user->id;
            $form->number = $award->number;
            $form->type = $award->type;
            $form->day = $this->day;
            $form->status = $this->status;
            $form->is_delete = 0;
            $form->token = $this->token;
            $form->award_id = $award->id;
            if (!$form->save()) {
                throw new \Exception($this->responseErrorMsg($form));
            }
            switch ($form->type) {
                case 'integral':
                    \Yii::$app->currency->setUser($this->user)->integral
                        ->add(intval($form->number), '签到赠送积分' . $form->number);
                    break;
                case 'balance':
                    \Yii::$app->currency->setUser($this->user)->balance
                        ->add(floatval($form->number), '签到赠送余额' . $form->number . '元');
                    break;
                default:
                    throw new \Exception('错误的奖励类型');
            }
            $t->commit();
            return $form;
        } catch (\Exception $exception) {
            $t->rollBack();
            throw $exception;
        }
    }

    /**
     * @return bool
     * @throws \Exception
     * 其他信息保存
     */
    public function otherSave()
    {
        return true;
    }
}
