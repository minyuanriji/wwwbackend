<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-11
 * Time: 15:38
 */

namespace app\plugins\stock\forms\mall;


use app\core\ApiCode;
use app\models\User;
use app\plugins\stock\forms\common\StockLevelCommon;
use app\plugins\stock\models\Stock;
use app\models\BaseModel;
use app\plugins\stock\models\StockAgent;

class StockUserEditForm extends BaseModel
{

    public $keyword;
    public $id;
    public $level;
    public $batch_ids;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['id', 'level'], 'integer'],
            [['batch_ids'], 'safe']
        ];// TODO: Change the autogenerated stub
    }


    public function getUser()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = User::find()->alias('u')
            ->where(['u.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id,])
            ->andWhere(['u.is_inviter' => 1])
            ->keyword($this->keyword !== '', ['like', 'u.nickname', $this->keyword])
            ->apiPage(20)->select('u.id,u.nickname')->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list
            ]
        ];
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            /* @var User $user */
            $user = User::findOne(['id' => $this->id, 'is_delete' => 0]);
            if (!$user) {
                return ['code' => ApiCode::CODE_FAIL, 'msg' => '用户不存在'];
            }
            $agent = StockAgent::findOne(['user_id' => $user->id, 'is_delete' => 0]);
            if ($agent) {
                return ['code' => ApiCode::CODE_FAIL, 'msg' => '用户已经是代理商，请勿重复提交'];
            } else {
                $agent = new StockAgent();
                $agent->mall_id = $user->mall_id;
                $agent->user_id = $user->id;
                $agent->created_at = time();
            }
            $t = \Yii::$app->db->beginTransaction();
            try {
                $agent->level=$this->level;
                $agent->is_delete = 0;
                if ($agent->save()) {
                    if (!$user->is_inviter) {
                        $user->inviter_at=time();
                        if (!$user->save()) {
                            return $this->responseErrorMsg($user);
                        }
                    }
                    $t->commit();
                    return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
                } else {
                    return $this->responseErrorMsg($agent);
                }
            } catch (\Exception $exception) {
                $t->rollBack();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => $exception->getMessage()
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function changeLevel()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $common = StockLevelCommon::getInstance();
            $common->userId = $this->id;
            if ($this->level) {
                $agentLevel = $common->getAgentLevelByLevel($this->level);
                if (!$agentLevel) {
                    throw new \Exception('无效的代理商等级');
                }
            } else {
                $this->level = 0;
            }
            $res = $common->changeLevel($this->level,StockAgent::UPGRADE_STATUS_MANUAL);
            if ($res) {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '修改成功'
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $res
                ];
            }


        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }


    public function batchLevel()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->level) {
                $common = StockLevelCommon::getInstance();
                $agentLevel = $common->getAgentLevelByLevel($this->level);
                if (!$agentLevel) {
                    throw new \Exception('无效的代理商等级');
                }
            } else {
                $this->level = 0;
            }
            StockAgent::updateAll(
                ['level' => $this->level, 'upgrade_level_at' => time()],
                ['id' => $this->batch_ids]
            );
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '修改成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}