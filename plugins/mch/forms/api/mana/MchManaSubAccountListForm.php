<?php
namespace app\plugins\mch\forms\api\mana;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\MchAdminUser;

class MchManaSubAccountListForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'required'],
            [['page'], 'integer', 'min' => 1]
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = MchAdminUser::find()->alias("mau");
            $query->andWhere(["mau.mch_id" => MchAdminController::$adminUser['mch_id']]);
            $query->select(["mau.id", "mau.created_at", "mau.mobile"]);
            $query->orderBy("mau.id DESC");

            $list = $query->asArray()->page($pagination, 10, $this->page)->all();
            foreach($list as &$item){
                $item['user_id']    = $item['id'];
                $item['nickname']   = "u" . $item['mobile'];
                $item['avatar_url'] = "";
                $item['created_at'] = date("Y-m-d H:i:s", $item['created_at']);
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
                'msg' => $e->getMessage()
            ];
        }
    }
}