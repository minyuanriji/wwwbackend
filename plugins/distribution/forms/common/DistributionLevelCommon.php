<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商等级
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:45
 */

namespace app\plugins\distribution\forms\common;

use app\models\DistributionCash;

use app\models\Mall;
use app\models\BaseModel;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionLevel;

/**
 * Class CommonDistributionLevel
 * @package app\forms\common\share
 * @property Mall $mall
 * @property User $user
 * @property Distribution $score
 */
class DistributionLevelCommon extends BaseModel
{
    private static $instance;
    public $mall;
    public $user;
    public $userId;
    public $distribution;


    public static function getInstance($mall = null)
    {
        if (!self::$instance) {
            self::$instance = new self();
            if (!$mall) {
                $mall = \Yii::$app->mall;
            }
            self::$instance->mall = $mall;
        }
        return self::$instance;
    }

    public function getLevelWeights()
    {
        $list = DistributionLevel::find()->select('level')->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->column();

        $newList = [];
        for ($i = 1; $i <= 10; $i++) {
            $newList[] = [
                'name' => '权重' . $i,
                'level' => $i,
                'disabled' => in_array($i, $list),
            ];
        }
        return $newList;
    }

    /**
     * 详情
     * @param $id
     * @return DistributionLevel|null
     */
    public function getDetail($id)
    {
        if (!$id) {
            return null;
        }
        /* @var DistributionLevel $distributionLevel */
        $distributionLevel = DistributionLevel::findOne([
            'id' => $id,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ]);
        return $distributionLevel;
    }

    /**
     * 删除
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $distributionLevel = $this->getDetail($id);
        if (!$distributionLevel) {
            throw new \Exception('所选择的分销商等级不存在或已删除，请刷新后重试');
        }
        $distributionExists = Distribution::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'level' => $distributionLevel->level
        ])->exists();
        if ($distributionExists) {
            throw new \Exception('该分销商等级下还有分销商存在，暂时不能删除');
        }
        $distributionLevel->is_delete = 1;
        if (!$distributionLevel->save()) {
            throw new \Exception($this->responseErrorMsg($distributionLevel));
        }
        return true;
    }





    /**
     * @param $level
     * @return bool
     * @throws \Exception
     */
    public function changeLevel($level,$upgrade_status)
    {
        $distribution = $this->getDistribution();
        if (!$distribution) {
            throw new \Exception('分销商不存在');
        }
        $distribution->level = $level;

        if($upgrade_status){
            $distribution->upgrade_status=$upgrade_status;

        }

        $distribution->upgrade_level_at = time();
        if (!$distribution->save()) {
            \Yii::error('升级分销商设置等级出错');
            throw new \Exception($this->responseErrorMsg($distribution));

        }
        return true;
    }

    /**
     * 获取分销商
     * @return Distribution|null
     * @throws \Exception
     *
     */
    private function getDistribution()
    {
        if ($this->distribution) {
            return $this->distribution;
        }
        $distribution = Distribution::findOne([
            'user_id' => $this->userId, 'is_delete' => 0, 'mall_id' => $this->mall->id
        ]);
        if (!$distribution) {
            throw new \Exception('不存在分销商');
        }
        $this->distribution = $distribution;
        return $distribution;
    }


    protected $DistributionLevelList;

    /**
     * 通过分销商等级来获取分销等级
     * @param $level
     * @return DistributionLevel|null
     *
     */
    public function getDistributionLevelByLevel($level)
    {
        if (!$level) {
            return null;
        }
        if (isset($this->DistributionLevelList[$level]) && $this->DistributionLevelList[$level]) {
            return $this->DistributionLevelList[$level];
        }
        /* @var DistributionLevel $distributionLevel */
        $distributionLevel = DistributionLevel::find()->where([
            'level' => $level,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->one();

        $this->DistributionLevelList[$level] = $distributionLevel;
        return $distributionLevel;
    }

    public function getList()
    {
        $levelList = [];
        if (\Yii::$app->mchId == 0) {
            $levelList = DistributionLevel::find()->where([
                'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_use' => 1,
            ])->select(['id', 'level', 'name'])->orderBy(['level' => SORT_ASC])->all();
        }
        array_unshift($levelList, [
            'id' => 0,
            'level' => 0,
            'name' => '默认等级'
        ]);
        return $levelList;
    }


    public static function getEnableLevelList()
    {
        $levelList = DistributionLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_use' => 1,
        ])->select(['id', 'level', 'name'])->orderBy(['level' => SORT_ASC])->all();

        array_unshift($levelList, [
            'id' => 0,
            'level' => 0,
            'name' => '默认等级'
        ]);
        return $levelList;
    }


}
