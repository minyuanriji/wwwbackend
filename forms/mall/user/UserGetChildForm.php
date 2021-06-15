<?php
namespace app\forms\mall\user;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\models\UserRelationshipLink;

class UserGetChildForm extends BaseModel{

    public $page;
    public $parent_id;
    public $keyword;
    public $role_type;
    public $start_date;
    public $end_date;

    public function rules(){
        return [
            [['parent_id'], 'required'],
            [['keyword', 'start_date', 'end_date', 'role_type'], 'trim'],
            [['page'], 'safe']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $userInfo = User::find()->alias("u")
                        ->innerJoin(["url" => UserRelationshipLink::tableName()], "url.user_id=u.id")
                        ->where(["u.id" => (int)$this->parent_id])
                        ->select(["u.*", "url.left", "url.right"])->asArray()->one();
            if(!$userInfo){
                throw new \Exception("用户不存在");
            }

            $query = User::find()->alias("u");
            $query->innerJoin(["url" => UserRelationshipLink::tableName()], "url.user_id=u.id");
            $query->andWhere([
                "AND",
                "url.left > '".$userInfo['left']."'",
                "url.right < '".$userInfo['right']."'"
            ]);

            $query->keyword($this->keyword, [
                'OR',
                ['like', 'u.nickname', $this->keyword],
                ['like', 'u.mobile', $this->keyword],
                ['like', 'u.id', $this->keyword],
            ]);

            if ($this->start_date && $this->end_date) {
                $query->andWhere(['<', 'u.created_at', strtotime($this->end_date)])
                    ->andWhere(['>', 'u.created_at', strtotime($this->start_date)]);
            }

            if($this->role_type){
                $query->andWhere(["u.role_type" => $this->role_type]);
            }

            $list = $query->select(['u.id', 'u.role_type',  'u.avatar_url', 'u.nickname', 'u.mobile', 'u.created_at'])
                        ->page($pagination, 20, $this->page)
                        ->orderBy('u.id DESC')
                        ->asArray()
                        ->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list,
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}