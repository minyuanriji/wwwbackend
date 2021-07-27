<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 成为分销商
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;

use app\core\ApiCode;
use app\forms\common\distribution\DistributionCommon;
use app\forms\common\distribution\DistributionLevelCommon;
use app\models\BaseModel;
use app\models\Distribution;
use app\models\DistributionLevel;
use app\models\User;

class EditForm extends BaseModel
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
        ];
    }

    public function getUser()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = User::find()->alias('u')->where(['u.is_delete' => 0, 'u.mall_id' => \Yii::$app->mall->id,])
                            ->andWhere(['u.is_distributor' => 0])
                            ->keyword($this->keyword !== '', ['like', 'u.nickname', $this->keyword])
                            ->apiPage(20)->select('u.id,u.nickname')->all();
        array_walk($list, function (&$item) {
            $platform = $item->userInfo ? $item->userInfo->platform : '';
            $item->nickname = User::getPlatformText($platform) . '（' . $item->nickname . '）';
        });
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
            if (!$this->id || $this->id < 0) {
                throw new \Exception('错误的用户');
            }
            /* @var User $user */
            $user = User::find()->where(['id' => $this->id])->with('share')->one();
            if (!$user) {
                throw new \Exception('用户不存在');
            }
            if ($user->is_distributor == 1) {
                throw new \Exception('所选用户已经是分销商，无需重复添加');
            }
            if ($user->distribution && $user->distribution->status == Distribution::STATUS_APPLY_ING) {
                throw new \Exception('用户已经提交分销商申请，请前往审核');
            }
            $commonDistribution = DistributionCommon::getCommon();
            $commonDistribution->becomeDistribution($user, ['status' => 1, 'reason' => '后台添加成为分销商']);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '添加成功'
            ];
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
            $common = DistributionLevelCommon::getInstance();
            $common->userId = $this->id;
            if ($this->level) {
                $distributionLevel = $common->getDistributionLevelByLevel($this->level);
                if (!$distributionLevel) {
                    throw new \Exception('无效的分销商等级');
                }
            } else {
                $this->level = 0;
            }
            $common->changeLevel($this->level);
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

    public function getLevel()
    {
        $level = DistributionLevel::find()
            ->where(['is_delete' => 0, 'status' => 1, 'mall_id' => \Yii::$app->mall->id])
            ->orderBy(['level' => SORT_ASC])
            ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $level
            ]
        ];
    }

    public function batchLevel()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->level) {
                $common = DistributionLevelCommon::getInstance();
                $distributionLevel = $common->getDistributionLevelByLevel($this->level);
                if (!$distributionLevel) {
                    throw new \Exception('无效的分销商等级');
                }
            } else {
                $this->level = 0;
            }
            Distribution::updateAll(
                ['level' => $this->level, 'level_at' => time()],
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
