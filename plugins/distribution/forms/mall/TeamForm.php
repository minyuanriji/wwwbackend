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
use app\plugins\distribution\models\Team;


class TeamForm extends BaseModel
{

    public $keyword;
    public $page;
    public $id;
    public $level;
    public $price_type;
    public $price;
    public $is_enable;
    public $parent_level;
    public $child_level;
    public $name;


    public function rules()
    {
        return [
            [['keyword', 'name'], 'string'],
            [['keyword', 'name'], 'trim'],
            [['price'], 'number'],
            [['page', 'id', 'is_enable', 'parent_level', 'child_level', 'price_type'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['name'], 'string'],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = Team::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
        ]);
        if ($this->keyword) {
            $query = $query->keyword($this->keyword, ['or', ['like', 'name', $this->keyword], ['like', 'parent_level', $this->keyword]]);
        }
        $list = $query->page($pagination, 20, $this->page)->orderBy(['created_at' => SORT_ASC])->asArray()->all();
        foreach ($list as &$item) {
            $parentLevel = DistributionLevel::findOne(['level' => $item['parent_level'], 'is_delete' => 0, 'mall_id' => $item['mall_id']]);
            if ($parentLevel) {
                $item['parent_level_name'] = $parentLevel->name;
            } else {
                $item['parent_level_name'] = '未知等级';
            }
            $childLevel = DistributionLevel::findOne(['level' => $item['child_level'], 'is_delete' => 0, 'mall_id' => $item['mall_id']]);
            if ($childLevel) {
                $item['child_level_name'] = $childLevel->name;
            } else {
                $item['child_level_name'] = '未知等级';
            }
            $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
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
    public function saveTeam()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        if ($this->id) {
            $extra = Team::find()->where(['parent_level' => $this->parent_level, 'child_level' => $this->child_level, 'is_delete' => 0])->andWhere(['!=', 'id', $this->id])->exists();
            if ($extra) {
                return ['code' => ApiCode::CODE_FAIL, 'msg' => '该等级配置已经存在！'];
            }
        }
        $extra = Team::findOne(['id' => $this->id, 'is_delete' => 0]);
        if (!$extra) {
            $extra = Team::findOne(['parent_level' => $this->parent_level, 'child_level' => $this->child_level, 'is_delete' => 0]);
        }
        if ($extra && $this->id != $extra->id) {
            return ['code' => ApiCode::CODE_FAIL, 'msg' => '该等级配置已经存在！'];
        }
        if (!$extra) {
            $extra = new Team();
            $extra->mall_id = \Yii::$app->mall->id;
        }
        $extra->attributes = $this->attributes;
        if (!$extra->save()) {
            return $this->responseErrorMsg($extra);
        }
        return ['code' => ApiCode::CODE_SUCCESS, 'msg' => '保存成功'];
    }


}