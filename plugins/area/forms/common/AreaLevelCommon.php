<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商等级
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:45
 */

namespace app\plugins\area\forms\common;

 

use app\models\Mall;
use app\models\BaseModel;
use app\models\User;
use app\plugins\area\models\AreaAgent;
 

/**
 * Class CommonDistributionLevel
 * @package app\forms\common\share
 * @property Mall $mall
 * @property User $user
 */
class AreaLevelCommon extends BaseModel
{
    private static $instance;
    public $mall;
    public $user;
    public $userId;
    public $area;

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

    /**
     * @param $level
     * @return bool
     * @throws \Exception
     */
    public function changeLevel($level,$upgrade_status,$province_id, $city_id, $district_id, $town_id)
    {
        $area = $this->getAreaAgent();
        if (!$area) {
            throw new \Exception('分销商不存在');
        }
        $area->level = $level;
        if($upgrade_status){
            $area->upgrade_status=$upgrade_status;
        }
        $area->province_id=$province_id;
        $area->city_id=$city_id;
        $area->district_id=$district_id;
        $area->town_id = $town_id;
        if (!$area->save()) {
            \Yii::error('升级分销商设置等级出错');
            throw new \Exception($this->responseErrorMsg($area));

        }
        return true;
    }

    /**
     * 获取分销商
     * @return AreaAgent|null
     * @throws \Exception
     *
     */
    private function getAreaAgent()
    {
        if ($this->area) {
            return $this->area;
        }
        $area = AreaAgent::findOne([
            'user_id' => $this->userId, 'is_delete' => 0, 'mall_id' => $this->mall->id
        ]);
        if (!$area) {
            throw new \Exception('不存在分销商');
        }
        $this->area = $area;
        return $area;
    }


}
