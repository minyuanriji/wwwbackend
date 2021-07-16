<?php
namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\MchSubAccount;

class MchSubAccountListForm extends BaseModel{

    public $mch_id;
    public $page;

    public function rules(){
        return [
            [['mch_id', 'page'], 'required'],
            [['page'], 'integer', 'min' => 1]
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = MchSubAccount::find()->alias("msa");
            $query->innerJoin(["u" => User::tableName()], "u.id=msa.user_id");

            $query->andWhere(["msa.mch_id" => $this->mch_id]);

            $query->select(["msa.created_at", "u.id as user_id", "u.mobile", "u.nickname", "u.avatar_url"]);
            $query->orderBy("msa.id DESC");

            $list = $query->asArray()->page($pagination, 10, $this->page)->all();
            foreach($list as &$item){
                $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list,
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }
}