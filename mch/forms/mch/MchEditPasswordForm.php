<?php
namespace app\mch\forms\mch;


use app\core\ApiCode;
use app\models\BaseModel;

class MchEditPasswordForm extends BaseModel {

    public $old_password;
    public $new_password;

    public function attributeLabels()
    {
        return [
            'old_password' => '原密码',
            'new_password' => '新密码'
        ];
    }

    public function rules(){
        return array_merge(parent::rules(), [
            [['old_password', 'new_password'], 'required'],
            [['old_password', 'new_password'], 'string', 'max' => 65]
        ]);
    }

    public function save(){
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $security = \Yii::$app->getSecurity();
        $mchAdmin = \Yii::$app->mchAdmin->identity;

        try {

            if(!$security->validatePassword($this->old_password, $mchAdmin->password)){
                throw new \Exception('原密码不正确');
            }

            $mchAdmin->password = $security->generatePasswordHash($this->new_password);
            $mchAdmin->auth_key = $security->generateRandomString();
            $mchAdmin->access_token = $security->generateRandomString();

            if (!$mchAdmin->save()) {
                throw new \Exception($this->responseErrorMsg($mchAdmin));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}