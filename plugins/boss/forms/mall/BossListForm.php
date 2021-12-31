<?php

namespace app\plugins\boss\forms\mall;


use app\forms\mall\export\BossExport;
use app\helpers\ArrayHelper;
use app\models\User;
use app\plugins\boss\forms\common\BossLevelCommon;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;
use app\models\BaseModel;

class BossListForm extends BaseModel
{

    public $keyword;
    public $platform;
    public $limit = 10;
    public $page = 1;
    public $sort;
    public $level_id;
    public $fields;
    public $flag;
    public $kw_type;

    public function rules()
    {
        return [
            [['keyword', 'platform'], 'trim'],
            [['keyword', 'platform', 'flag', 'kw_type'], 'string'],
            [['limit', 'page', 'level_id'], 'integer'],
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
        $query = Boss::find()->alias('d')->with(['user'])
            ->where(['d.is_delete' => 0, 'd.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id = d.user_id')
            ->andWhere(['and', ['!=', 'u.mobile', ''], ['IS NOT', 'u.mobile', NULL], ['u.is_delete' => 0]]);

        if ($this->keyword && $this->kw_type) {
            switch ($this->kw_type)
            {
                case 'mobile':
                    $query->andWhere(['u.mobile' => $this->keyword]);
                    break;
                case 'user_id':
                    $query->andWhere(['d.user_id' => $this->keyword]);
                    break;
                case 'nickname':
                    $query->andWhere(['like', 'u.nickname', $this->keyword]);
                    break;
                default:
            }
        }

        if ($this->level_id) {
            $query->andWhere(['d.level_id' => $this->level_id]);
        }
        if ($this->flag == "EXPORT") {
            $new_query = clone $query;
            $exp = new BossExport();
            $exp->fieldsKeyList = $this->fields;
            $exp->export($new_query, 'd.');
            return false;
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->orderBy($this->sort)->all();
        $newList = [];
        /* @var Boss[] $list */
        foreach ($list as $item) {
            $newItem = ArrayHelper::toArray($item);
            /* @var User $user */
            $user = $item->user;
            $newItem = array_merge($newItem, [
                'nickname' => $user[0]->nickname,
                'avatar_url' => $user[0]->avatar_url,
                'mobile' => $user[0]->mobile,
//                'parent_name' => $user[0]->parent ? $user['parent']['nickname'] : '平台',
            ]);
            $newItem['userInfo'] = ArrayHelper::toArray($item->user);
            $bossLevel = BossLevel::find()->where([
                'id' => $item->level_id,
                'is_delete' => 0
            ])->one();
            if (!$bossLevel) {
                $newItem['level_name'] = '';
            } else {
                $newItem['level_name'] = $bossLevel->name;
            }
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'export_list' => (new BossExport())->fieldsList(),
                'list' => $newList,
                'pagination' => $pagination,
                'bossLevelList' => BossLevelCommon::getInstance()->getList(),
            ]
        ];
    }
}