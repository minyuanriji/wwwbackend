<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\MchAdminUser;

class MchManaAccountSetPwdForm extends BaseModel {

    public $username;
    public $password;

    public function rules(){
        return array_merge(parent::rules(), [
            [['password'], 'required'],
            [['password', 'username'], 'trim'],
            [['username'], 'safe']
        ]);
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $security = \Yii::$app->getSecurity();

            $adminUser = MchAdminUser::findOne(["mch_id" => MchAdminController::$adminUser['mch_id']]);
            if(!$adminUser){
                $adminUser = new MchAdminUser([
                    "mall_id" => MchAdminController::$adminUser['mall_id'],
                    "mch_id" => MchAdminController::$adminUser['mch_id'],
                    "created_at" => time()
                ]);
            }

            if(empty($adminUser->username)){
                if(empty($this->username)){
                    throw new \Exception("账号不能为空");
                }
                $len = strlen($this->username);
                if($len < 3 || $len > 20){
                    throw new \Exception("请控制账号长度在3~20个字符之间");
                }
                if(MchAdminUser::findOne(["username" => $this->username])){
                    throw new \Exception("账号“".$this->username."”已被使用");
                }
                $adminUser->username = $this->username;
            }

            if(strlen($this->password) < 3){
                throw new \Exception("密码长度不能小于3个字符");
            }

            $adminUser->updated_at = time();
            $adminUser->password   = $security->generatePasswordHash($this->password);
            if(!$adminUser->save()){
                throw new \Exception($this->responseErrorMsg($adminUser));
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