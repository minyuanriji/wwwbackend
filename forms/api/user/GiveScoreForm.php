<?php
namespace app\forms\api\user;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;

class GiveScoreForm extends BaseModel{

    public $user_id;
    public $number;
    public $desc;

    public function rules(){
        return [
            [['user_id', 'number'], 'required'],
            [['user_id', 'number'], 'integer'],
            [['desc'], 'string']
        ];
    }

    public function execute(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $user = User::findOne($this->user_id);
            if(!$user){
                throw new \Exception("用户不存在");
            }
            \Yii::$app->currency->setUser($user)->score->add((int)$this->number, $this->desc, json_encode([]));
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}