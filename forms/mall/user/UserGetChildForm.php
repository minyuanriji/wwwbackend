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

    public function rules(){
        return [
            [['parent_id'], 'required'],
            [['page', 'keyword'], 'safe']
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