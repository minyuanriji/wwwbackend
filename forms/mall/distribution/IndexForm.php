<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商列表
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;

use app\forms\common\distribution\DistributionTeamCommon;
use app\forms\common\distribution\DistributionLevelCommon;
use app\forms\mall\export\DistributionUserExport;
use app\models\BaseModel;
use app\models\Distribution;
use app\models\DistributionSetting;
use app\models\User;
use yii\helpers\ArrayHelper;

class IndexForm extends BaseModel
{
    public $keyword;
    public $status;
    public $platform;

    public $limit = 10;
    public $page = 1;
    public $sort;
    public $level;

    public $fields;
    public $flag;

    public function rules()
    {
        return [
            [['keyword', 'status', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['status', 'limit', 'page', 'level'], 'integer'],
            [['fields'], 'safe'],
            [['status'], 'default', 'value' => -1],
            [['sort'], 'default', 'value' => ['s.status' => SORT_ASC, 's.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = Distribution::find()->alias('s')->with(['user', 'order'])
            ->where(['s.is_delete' => 0, 's.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = s.user_id');

        $query->keyword($this->platform, ['ui.platform' => $this->platform]);

        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 's.name', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }

        switch ($this->status) {
            case 0:
                $query->andWhere(['s.status' => 0]);
                break;
            case 1:
                $query->andWhere(['s.status' => 1]);
                break;
            case 2:
                $query->andWhere(['s.status' => 2]);
                break;
            default:
                break;
        }


        if ($this->level !== '' && $this->level !== null) {
            $query->andWhere(['s.level' => $this->level]);
        }


        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new DistributionUserExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query);
            return false;
        }

        $list = $query->page($pagination, $this->limit, $this->page)
            ->with(['userInfo', 'shareLevel'])
            ->orderBy($this->sort)->all();
        $level = DistributionSetting::get(\Yii::$app->mall->id, DistributionSetting::LEVEL, 0);

        $newList = [];
        /* @var Distribution[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            /* @var User $user */
            $user = $item->user;

            $form = new DistributionTeamCommon();
            $form->mall = \Yii::$app->mall;

            $newItem = array_merge($newItem, [
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'parent_name' => $user->parent ? $user->parent->nickname : '平台',
            ]);
            if ($level > 0) {
                $newItem['first'] = count($form->info($item->user_id, 1));
                if ($level > 1) {
                    $newItem['second'] = count($form->info($item->user_id, 2));
                    if ($level > 2) {
                        $newItem['third'] = count($form->info($item->user_id, 3));
                    }
                }
            }
            $newItem['userInfo'] = ArrayHelper::toArray($item->user);
            $newItem['level_name'] = $item->distributionLevel ? $item->distributionLevel->name : '默认等级';
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'export_list' => (new DistributionUserExport())->fieldsList(),
                'distributionLevelList' => DistributionLevelCommon::getInstance()->getList(),
            ]
        ];
    }
}
