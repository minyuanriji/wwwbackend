<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 代理商等级
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:45
 */

namespace app\plugins\stock\forms\common;

use app\models\Mall;
use app\models\BaseModel;
use app\models\User;
use app\plugins\stock\models\StockAgent;

use app\plugins\stock\models\StockLevel;

/**
 * Class CommonAgentLevel
 * @package app\forms\common\share
 * @property Mall $mall
 * @property User $user
 * @property StockAgent $stock
 */
class StockLevelCommon extends BaseModel
{
    private static $instance;
    public $mall;
    public $user;
    public $userId;
    public $stock;
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
        $list = StockLevel::find()->select('level')->where([
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
     * @return StockLevel|null
     */
    public function getDetail($id)
    {
        if (!$id) {
            return null;
        }
        /* @var StockLevel $agentLevel */
        $agentLevel = StockLevel::findOne([
            'id' => $id,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ]);
        return $agentLevel;
    }

    /**
     * 删除
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $agentLevel = $this->getDetail($id);
        if (!$agentLevel) {
            throw new \Exception('所选择的代理商等级不存在或已删除，请刷新后重试');
        }
        $agentExists = StockAgent::find()->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'level' => $agentLevel->level
        ])->exists();
        if ($agentExists) {
            throw new \Exception('该代理商等级下还有代理商存在，暂时不能删除');
        }
        $agentLevel->is_delete = 1;
        if (!$agentLevel->save()) {
            throw new \Exception($this->responseErrorMsg($agentLevel));
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
        $agent = $this->getAgent();
        if (!$agent) {
            throw new \Exception('代理商不存在');
        }
        $agent->level = $level;

        if ($upgrade_status) {
            $agent->upgrade_status = $upgrade_status;
        }

        $agent->upgrade_level_at = time();
        if (!$agent->save()) {
            \Yii::error('升级代理商设置等级出错');
            throw new \Exception($this->responseErrorMsg($agent));

        }
        return true;
    }

    /**
     * 获取代理商
     * @return StockAgent|null
     * @throws \Exception
     *
     */
    private function getAgent()
    {
        if (!empty($this->stock)) {
            return $this->stock;
        }
        $agent = StockAgent::findOne([
            'user_id' => $this->userId, 'is_delete' => 0, 'mall_id' => $this->mall->id
        ]);
        if (!$agent) {
            throw new \Exception('不存在代理商');
        }
        $this->stock = $agent;
        return $agent;
    }


    protected $AgentLevelList;

    /**
     * 通过代理商等级来获取经销等级
     * @param $level
     * @return StockLevel|null
     *
     */
    public function getAgentLevelByLevel($level)
    {
        if (!$level) {
            return null;
        }
        if (isset($this->AgentLevelList[$level]) && $this->AgentLevelList[$level]) {
            return $this->AgentLevelList[$level];
        }
        /* @var StockLevel $agentLevel */
        $agentLevel = StockLevel::find()->where([
            'level' => $level,
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ])->one();

        $this->AgentLevelList[$level] = $agentLevel;
        return $agentLevel;
    }

    public function getList()
    {
        $levelList = [];
        $levelList = StockLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'is_use' => 1,
        ])->select(['id', 'level', 'name'])->orderBy(['level' => SORT_ASC])->all();
        array_unshift($levelList, [
            'id' => 0,
            'level' => 0,
            'name' => '默认等级'
        ]);
        return $levelList;
    }


    public static function getEnableLevelList()
    {
        $levelList = StockLevel::find()->where([
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
