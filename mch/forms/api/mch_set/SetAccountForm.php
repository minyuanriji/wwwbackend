<?php
namespace app\mch\forms\api\mch_set;

use app\core\ApiCode;
use app\models\Admin;
use app\models\BaseModel;

class SetAccountForm extends BaseModel{

    public $mall_id;
    public $mch_id;
    public $username;
    public $password;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id', 'mall_id', 'username', 'password'], 'required']
        ]);
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $security = \Yii::$app->getSecurity();
            $adminModel = Admin::findOne(["mch_id" => $this->mch_id]);
            if(!$adminModel || empty($adminModel->username)){
                if(empty($this->username)){
                    throw new \Exception("账号不能为空");
                }
                $len = strlen($this->username);
                if($len < 3 || $len > 15){
                    throw new \Exception("请控制账号长度在3~15个字符之间");
                }
                $exists = Admin::find()->where(["username" => $this->username])->exists();
                if($exists){
                    throw new \Exception("账号“".$this->username."”已被使用");
                }
                if(!$adminModel){
                    $adminModel = new Admin([
                        "mall_id"      => $this->mall_id,
                        "mch_id"       => $this->mch_id,
                        "auth_key"     => $security->generatePasswordHash(uniqid()),
                        "access_token" => $security->generatePasswordHash(uniqid()),
                        "admin_type"   => 3,
                        "created_at"   => time(),
                        "updated_at"   => time()
                    ]);
                }
            }

            $adminModel->username = $this->username;
            $adminModel->password = $security->generatePasswordHash($this->password);
            if(!$adminModel->save()){
                throw new \Exception($this->responseErrorMsg($adminModel));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

}