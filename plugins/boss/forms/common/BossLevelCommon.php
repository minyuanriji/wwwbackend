<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 股东等级
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:45
 */

namespace app\plugins\boss\forms\common;

use app\models\Mall;
use app\models\BaseModel;
use app\models\User;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;

/**
 * Class CommonBossLevel
 * @package app\forms\common\share
 * @property Mall $mall
 * @property User $user
 * @property Boss $boss
 */
class BossLevelCommon extends BaseModel
{
    private static $instance;
    public $mall;
    public $user;
    public $userId;
    public $boss;


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
        $list = BossLevel::find()->select('level')->where([
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ])->column();
        $newList = [];
        for ($i = 1; $i <= 10; $i++) {
            $newList[] = [
                'name' => '等级' . $i,
                'level' => $i,
                'disabled' => in_array($i, $list),
            ];
        }
        return $newList;
    }

    /**
     * 详情
     * @param $id
     * @return BossLevel|null
     */
    public function getDetail($id)
    {
        if (!$id) {
            return null;
        }
        /* @var BossLevel $bossLevel */
        $bossLevel = BossLevel::findOne([
            'id' => $id,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ]);
        return $bossLevel;
    }

    /**
     * 删除
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $bossLevel = $this->getDetail($id);
        if (!$bossLevel) {
            throw new \Exception('所选择的股东等级不存在或已删除，请刷新后重试');
        }
        $bossExists = Boss::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'level' => $bossLevel->level
        ])->exists();
        if ($bossExists) {
            throw new \Exception('该股东等级下还有股东存在，暂时不能删除');
        }
        $bossLevel->is_delete = 1;
        if (!$bossLevel->save()) {
            throw new \Exception($this->responseErrorMsg($bossLevel));
        }
        return true;
    }


    /**
     * @param $level
     * @return bool
     * @throws \Exception
     */
    public function changeLevel($level, $upgrade_status)
    {
        $boss = $this->getBoss();
        if (!$boss) {
            throw new \Exception('股东不存在');
        }
        $boss->level = $level;

        if ($upgrade_status) {
            $boss->upgrade_status = $upgrade_status;
        }

        $boss->upgrade_level_at = time();
        if (!$boss->save()) {
            \Yii::error('升级股东设置等级出错');
            throw new \Exception($this->responseErrorMsg($boss));

        }
        return true;
    }

    /**
     * 获取股东
     * @return Boss|null
     * @throws \Exception
     *
     */
    private function getBoss()
    {
        if ($this->boss) {
            return $this->boss;
        }
        $boss = Boss::findOne([
            'user_id' => $this->userId, 'is_delete' => 0, 'mall_id' => $this->mall->id
        ]);
        if (!$boss) {
            throw new \Exception('不存在股东');
        }
        $this->boss = $boss;
        return $boss;
    }


    protected $BossLevelList;

    /**
     * 通过股东等级来获取经销等级
     * @param $level
     * @return BossLevel|null
     *
     */
    public function getBossLevelByLevel($level)
    {
        if (!$level) {
            return null;
        }
        if (isset($this->BossLevelList[$level]) && $this->BossLevelList[$level]) {
            return $this->BossLevelList[$level];
        }
        /* @var BossLevel $bossLevel */
        $bossLevel = BossLevel::find()->where([
            'level' => $level,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->one();

        $this->BossLevelList[$level] = $bossLevel;
        return $bossLevel;
    }

    public function getList()
    {
        $levelList = BossLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, //'is_enable' => 1,
        ])->select(['id', 'level', 'name'])->orderBy(['id' => SORT_ASC])->all();
        /*array_unshift($levelList, [
            'id' => 0,
            'level' => 0,
            'name' => '默认等级'
        ]);*/
        return $levelList;
    }


    public static function getEnableLevelList()
    {
        $levelList = BossLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, //'is_enable' => 1,
        ])->select(['id', 'level', 'name'])->orderBy(['level' => SORT_ASC])->all();

        /*array_unshift($levelList, [
            'id' => 0,
            'level' => 0,
            'name' => '默认等级'
        ]);*/
        return $levelList;
    }


}
