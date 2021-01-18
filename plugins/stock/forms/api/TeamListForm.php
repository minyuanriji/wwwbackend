<?php
/**
 * Created by PhpStorm.
 * User: kaifa
 * Date: 2020-05-10
 * Time: 17:57
 */

namespace app\plugins\stock\forms\api;

use app\models\Goods;
use app\models\User;
use app\models\UserChildren;
use app\plugins\stock\models\FillIncomeLog;
use app\plugins\stock\models\FillOrder;
use app\plugins\stock\models\FillOrderDetail;
use app\plugins\stock\models\GoodsPriceLog;
use app\models\BaseModel;
use app\plugins\stock\models\StockAgent;

class TeamListForm extends BaseModel
{
    public $limit = 10;
    public $page = 1;
    public $level=0;

    public function rules()
    {
        return [
            [['limit', 'page','level'], 'integer'],

        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mall = \Yii::$app->mall;
        $pagination = null;
        $query = StockAgent::find()
            ->alias('sa')
            ->where(['sa.is_delete' => 0, 'sa.mall_id' => $mall->id])
            ->leftJoin(['uc' => UserChildren::tableName()], 'uc.child_id=sa.user_id')
            ->leftJoin(['u' => User::tableName()], 'u.id=uc.child_id')
            ->andWhere(['uc.is_delete' => 0, 'uc.user_id' => \Yii::$app->user->identity->id, 'sa.is_delete' => 0]);
        if($this->level){
            $query->andWhere(['sa.level'=>$this->level]);
        }
        $list = $query->page($pagination, $this->limit, $this->page)
            ->select('sa.*,u.nickname,u.avatar_url,u.mobile')
            ->orderBy('sa.id desc')->asArray()->all();
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
                'pagination' => $pagination,
            ]
        ];
    }
}