<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:53
 */

namespace app\plugins\distribution\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\distribution\models\DistributionLevel;
use app\plugins\distribution\models\DistributionSetting;
use app\plugins\distribution\models\RebuyLevel;
use yii\base\Theme;

class RebuyLevelForm extends BaseModel
{

    public $keyword;
    public $page;
    public $id;
    public $level;
    public $price_type;
    public $price;
    public $is_enable;
    public $distribution_level;
    public $name;
    public $child_num;
    public $upgrade_type;
    public $team_child_num;

    public function rules()
    {
        return [
            [['keyword', 'name'], 'string'],
            [['keyword', 'name'], 'trim'],
            [['price'], 'number'],
            [['page', 'id', 'is_enable', 'distribution_level', 'level', 'price_type', 'upgrade_type', 'child_num','team_child_num'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['team_child_num'],'default','value'=>-1]
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = RebuyLevel::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
        ]);
        if ($this->keyword) {
            $query = $query->keyword($this->keyword, ['or', ['like', 'name', $this->keyword], ['like', 'level', $this->keyword]]);
        }
        $list = $query->page($pagination, 20, $this->page)->orderBy(['level' => SORT_ASC])->asArray()->all();
        foreach ($list as &$item) {
            $distributionLevel = DistributionLevel::findOne(['level' => $item['distribution_level'], 'is_delete' => 0, 'mall_id' => $item['mall_id']]);
            if ($distributionLevel) {
                $item['distribution_level_name'] = $distributionLevel->name;
            } else {
                $item['distribution_level_name'] = '未知等级';
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $list,
                'pagination' => $pagination,
            ]
        ];
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-07-20
     * @Time: 16:34
     * @Note:保存等级
     */
    public function saveLevel()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $level = RebuyLevel::findOne(['level' => $this->level, 'is_delete' => 0]);
        if ($level && !$this->id) {
            return ['code' => ApiCode::CODE_FAIL, 'msg' => '该等级已经存在！'];
        }
        if (!$level) {
            $level = new RebuyLevel();
            $level->mall_id = \Yii::$app->mall->id;
        }
        $level->attributes = $this->attributes;
        if (!$level->save()) {
            return $this->responseErrorMsg($level);
        }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
    }


}