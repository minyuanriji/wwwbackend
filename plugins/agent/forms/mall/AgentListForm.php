<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\agent\forms\mall;


use app\helpers\ArrayHelper;
use app\models\User;
use app\plugins\agent\forms\common\AgentLevelCommon;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentSetting;
use app\models\BaseModel;

class AgentListForm extends BaseModel
{

    public $keyword;
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
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag'], 'string'],
            [['limit', 'page', 'level'], 'integer'],
            [['fields'], 'safe'],
            [['sort'], 'default', 'value' => ['d.created_at' => SORT_DESC]],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = Agent::find()->alias('d')->with(['user'])
            ->where(['d.is_delete' => 0, 'd.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = d.user_id');
        if ($this->keyword) {
            $query->andWhere([
                'or',
                ['like', 'u.username', $this->keyword],
                ['like', 'u.nickname', $this->keyword]
            ]);
        }
        if ($this->level) {
            $query->andWhere(['d.level' => $this->level]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->all();
        $newList = [];
        /* @var Agent[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            /* @var User $user */
            $user = $item->user;
            $newItem = array_merge($newItem, [
                'nickname' => $user['nickname'],
                'avatar_url' => $user['avatar_url'],
                'parent_name' => $user['parent'] ? $user['parent']['nickname'] : '平台',
            ]);
            $newItem['userInfo'] = ArrayHelper::toArray($item->user);
            $agent_level=null;
            if ($item->level > 0) {
                $common = AgentLevelCommon::getInstance();
                $agent_level = $common->getAgentLevelByLevel($item->level);
                if (!$agent_level) {
                    throw new \Exception('无效的分销商等级');
                }
            }
            $newItem['level_name'] = $agent_level ? $agent_level->name : '默认等级';
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'pagination' => $pagination,
                'agentLevelList' => AgentLevelCommon::getInstance()->getList(),
            ]
        ];
    }
}