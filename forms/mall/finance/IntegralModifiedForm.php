<?php
namespace app\forms\mall\finance;

use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\BaseModel;
use app\models\User;

class IntegralModifiedForm extends BaseModel{

    public $type;
    public $user_id;
    public $price;
    public $remark;

    public function rules(){
        return [
            [['type', 'user_id', 'price'], 'required'],
            [['remark'], 'safe']
        ];
    }

    public function modified(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $user = User::findOne((int)$this->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            if($this->price <= 0){
                throw new \Exception("金额不能小于0");
            }

            $adminId = \Yii::$app->admin->id;

            if($this->type == 1){ //充值
                $res = UserIntegralForm::adminAdd($user, $this->price, $adminId, $this->remark);
            }else{ //扣减
                if ($this->price > $user->static_integral) {
                    throw new \Exception("最多扣除金豆" . $user->static_integral);
                }
                $res = UserIntegralForm::adminSub($user, $this->price, $adminId, $this->remark);
            }

            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}