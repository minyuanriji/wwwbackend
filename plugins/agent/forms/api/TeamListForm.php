<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\agent\forms\api;


use app\models\User;
use app\models\UserChildren;
use app\models\BaseModel;
use app\plugins\agent\forms\common\Common;
use app\plugins\agent\models\Agent;


class TeamListForm extends BaseModel
{
    public $limit = 10;
    public $page = 1;
    public $level = 0;
    //0全部1直推或2间推
    public $flag = 1;

    public function rules()
    {
        return [
            [['limit', 'page','level','flag'], 'integer'],

        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = UserChildren::find()
            ->alias('uc')
            ->leftJoin(['b' => Agent::tableName()], 'uc.child_id=b.user_id')
            ->where(['b.is_delete' => 0, 'b.mall_id' => $mall->id])
            ->leftJoin(['u' => User::tableName()], 'u.id=uc.child_id')
            ->andWhere(['uc.is_delete' => 0, 'uc.user_id' => \Yii::$app->user->identity->id, 'b.is_delete' => 0]);


        if($this->level > 0){
            $query->andWhere(['b.level'=>$this->level]);
        }

        $directTotalQuery = clone $query;
        $spaceTotalQuery = clone $query;

        if($this->flag == 1){
            $query->andWhere(['uc.level'=> $this->flag]);
        }else if($this->flag == 2){
            $query->andWhere(['>=','uc.level' , $this->flag]);
        }

        $totalWheres = ["uc.level" => $this->flag];
        $direct_push_total = $directTotalQuery->andWhere($totalWheres)->count();
        $totalWheres = ['>=','uc.level' , $this->flag];
        $space_push_total = $spaceTotalQuery->andWhere($totalWheres)->count();

        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('b.*,u.nickname,u.avatar_url,u.mobile')
            ->orderBy('b.id desc')->asArray()->all();
        $newList = [];

        foreach ($list as $item) {
            $newItem['id'] = $item['id'];
            $newItem['user_id'] = $item['user_id'];
            $newItem['nickname'] = $item['nickname'];
            $newItem['avatar_url'] = $item['avatar_url'];
            $newItem['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
            $newItem['mobile'] = $item['mobile'];
            $newItem['total_price'] = $item['total_price'];
            $team_count = UserChildren::find()->where(['user_id' => $item['user_id'], 'is_delete' => 0])->count();
            $newItem['team_count'] = $team_count ?? 0;
            $first_team_count = UserChildren::find()->where(['user_id' => $item['user_id'], 'is_delete' => 0, 'level' => 1])->count();
            $newItem['first_team_count'] = $first_team_count ?? 0;
            $newItem['other_team_count'] = $newItem['team_count'] - $newItem['first_team_count'];
            $newList[] = $newItem;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'list' => $newList,
                'stat_data' => [
                    'direct_push_total' => intval($direct_push_total),
                    'space_push_total' => intval($space_push_total),
                ],
                'level_list' => Common::getAllLevelTotal(),
                'pagination' => $this->getPaginationInfo($pagination),
            ]
        ];
    }
}