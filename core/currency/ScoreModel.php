<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: zal
 * Date: 2020-04-15
 * Time: 17:01
 */

namespace app\core\currency;


use app\events\ScoreEvent;
use app\forms\common\template\tplmsg\AccountChange;
use app\models\BaseModel;
use app\models\ErrorLog;
use app\models\Mall;
use app\models\ScoreLog;
use app\models\User;
use yii\db\Exception;

/**
 * @property Mall $mall;
 * @property User $user;
 */
class ScoreModel extends BaseModel implements BaseCurrency
{
    public $mall;
    public $user;
    public $type;// 积分类型：1=收入，2=支出

    /**
     * @param $score
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    public function add($score, $desc, $customDesc = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_numeric($score)) {
            throw new Exception('积分必须是数值类型');
        }
        $score =  round($score, 2);
        $t = \Yii::$app->db->beginTransaction();
        $this->user->score += $score;
        $this->user->total_score += $score;
        if ($this->user->save()) {
            try {
                $this->createLog(1, $score, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($this->user), $this->user->errors, 1);
        }
    }

    /**
     * @param $score
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    public function sub($score, $desc, $customDesc = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_numeric($score)) {
            throw new Exception('积分必须是数值类型');
        }
        if ($this->user->score < $score) {
            throw new Exception('用户积分不足');
        }
        $score =  round($score, 2);
        $t = \Yii::$app->db->beginTransaction();
        $this->user->score -= $score;
        if ($this->user->save()) {
            try {
                $this->createLog(2, $score, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($this->user), $this->user->errors, 1);
        }
    }

    /**
     * @return integer
     */
    public function select()
    {
        return intval($this->user->score);
    }

    /**
     * @return integer
     */
    public function selectTotal()
    {
        return intval($this->user->total_score);
    }

    /**
     * @param $score
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws Exception
     */
    public function refund($score, $desc, $customDesc = '')
    {
        $this->mall = \Yii::$app->mall;
        if (!is_numeric($score)) {
            throw new Exception('积分必须是数值类型');
        }

        $score =  round($score, 2);
        $t = \Yii::$app->db->beginTransaction();
        $this->user->score += $score;
        if ($this->user->save()) {
            try {
                $this->createLog(1, $score, $desc, $customDesc);
                $t->commit();
                return true;
            } catch (Exception $e) {
                $t->rollBack();
                throw $e;
            }
        } else {
            $t->rollBack();
            throw new Exception($this->responseErrorMsg($this->user), $this->user->errors, 1);
        }
    }

    /**
     * @param $type
     * @param $score
     * @param $desc
     * @param string $customDesc
     * @return bool
     * @throws \Exception
     */
    private function createLog($type, $score, $desc, $customDesc = '')
    {
        if ($score == 0) {
            \Yii::warning('积分为' . $score . '不记录日志');
            return true;
        }
        if (!$customDesc) {
            $customDesc = \Yii::$app->serializer->encode(['msg' => '用户积分变动说明']);
        }

        $score =  round($score, 2);
        $form = new ScoreLog();
        $form->user_id = $this->user->id;
        $form->mall_id = $this->user->mall_id;
        $form->type = $type;
        $form->score = $score;
        $form->desc = $desc;
        $form->custom_desc = $customDesc;
        $form->current_score = $this->user->score;
        if ($form->save()) {
//            $templateSend = new AccountChange([
//                'remark' => '用户积分变动说明',
//                'desc' => $desc,
//                'page' => 'pages/user/index',
//                'user' => $this->user
//            ]);
//            $templateSend->send();
            //触发积分变动事件
            $event              = new ScoreEvent();
            $event->score_log = $form;
            \Yii::$app->trigger(ScoreLog::EVENT_SCORE_CHANGE, $event);
            return true;
        } else {
            throw new \Exception($this->responseErrorMsg($form), $form->errors, 1);
        }
    }

    public function getLogListByUser()
    {
        $list = ScoreLog::find()->where([
            'mall_id' => $this->mall->id,
            'user_id' => $this->user->id,
            'type' => $this->type,
        ])
            ->page($pagination)
            ->orderBy('created_at DESC')
            ->asArray()
            ->all();

        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}
