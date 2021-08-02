<?php
namespace app\forms\mall\user;

use app\core\ApiCode;
use app\forms\common\UserRelationshipLinkForm;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\models\UserRelationshipLink;
use app\plugins\mch\models\Mch;

class UserGetChildForm extends BaseModel{

    public $page;
    public $parent_id;
    public $keyword;
    public $role_type;
    public $start_date;
    public $end_date;
    public $team_type;

    public function rules(){
        return [
            [['parent_id'], 'required'],
            [['keyword', 'start_date', 'end_date', 'role_type', 'team_type'], 'trim'],
            [['page'], 'safe']
        ];
    }

    public function getList()
    {
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            //获取用户
            $user = User::findOne((int)$this->parent_id);
            $userLink = UserRelationshipLink::findOne(["user_id" => $user->id]);

            if ($this->team_type == 'direct_push') { //直推
                $query = UserRelationshipLinkForm::getDirectListQuery($user, $userLink);
            } else if ($this->team_type == 'Interpulsion') { //间推
                $query = UserRelationshipLinkForm::getSecondList($user, $userLink);
            } else {
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
            }

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

            $list = $query->select(['u.id', 'u.parent_id', 'u.role_type',  'u.avatar_url', 'u.nickname', 'u.mobile', 'u.created_at'])
                        ->page($pagination, 10, max(1, $this->page))
                        ->orderBy('u.id DESC')
                        ->asArray()
                        ->all();

            if ($list) {
                foreach ($list as &$item) {
                    //获取店铺名
                    $mch = Mch::findOne(['user_id' => $item['id']]);
                    if ($mch) {
                        $store = Store::findOne(['mch_id' => $mch->id]);
                        $item['store_name'] = $store ? $store->name : '无';
                    } else {
                        $item['store_name'] = '无';
                    }

                    if ($item['parent_id'] != $this->parent_id) {
                        $item['team_type'] = '间推';
                    } else {
                        $item['team_type'] = '直推';
                    }
                }
            }
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