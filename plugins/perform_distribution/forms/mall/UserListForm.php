<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\user\User;
use app\plugins\perform_distribution\models\Level;
use app\plugins\perform_distribution\models\PerformDistributionUser;

class UserListForm extends BaseModel{

    public $region_id;
    public $level_id;
    public $keyword;
    public $limit = 10;
    public $page = 1;

    public function rules(){
        return [
            [['region_id', 'level_id'], 'required'],
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['limit', 'page', 'region_id'], 'integer']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = PerformDistributionUser::find()->alias("pdu")
                ->where([
                    'pdu.level_id'  => $this->level_id,
                    'pdu.region_id' => $this->region_id,
                    'pdu.is_delete' => 0,
                    'pdu.mall_id'   => \Yii::$app->mall->id
                ])->leftJoin(["l" => Level::tableName()], "l.id=pdu.level_id")
                  ->leftJoin(["u" => User::tableName()], "u.id=pdu.user_id");

            if ($this->keyword) {
                $query->andWhere([
                    "OR",
                    ['u.id' => (int)$this->keyword],
                    ['like', 'u.nickname', $this->keyword],
                    ['like', 'u.mobile', $this->keyword]
                ]);
            }

            $list = $query->select('u.nickname,u.parent_id,u.mobile,u.avatar_url,pdu.*,l.name as level_name')
                ->page($pagination, $this->limit, $this->page)
                ->orderBy("pdu.id DESC")->asArray()->all();
            if($list){
                foreach($list as $key => $row){
                    $row['total_income'] = 0;
                    $list[$key] = $row;
                }
            }

            foreach ($list as $key => $item) {
                $user = User::findOne($item['user_id']);
                $item['user'] = [
                    "nickname"   => $user->nickname,
                    "mobile"     => $user->mobile,
                    "avatar_url" => $user->avatar_url
                ];
                //上级信息
                $parentInfo = User::find()->alias("u")
                    ->leftJoin(["pdu" => PerformDistributionUser::tableName()], "u.id=pdu.user_id")
                    ->leftJoin(["l" => Level::tableName()], "l.id=pdu.level_id")
                    ->where(['u.id' => $item['parent_id']])
                    ->asArray()->select("u.id, u.nickname,u.mobile,u.avatar_url,l.name as level_name")->one();
                $item['parent'] = $parentInfo ? $parentInfo : '';
                $list[$key] = $item;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'list'       => $list ? $list : [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}