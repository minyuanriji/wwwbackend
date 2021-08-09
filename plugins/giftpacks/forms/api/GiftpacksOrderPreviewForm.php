<?php

namespace app\plugins\giftpacks\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;

class GiftpacksOrderPreviewForm extends BaseModel{

    public $pack_id;

    public function rules(){
        return [
            [['pack_id'], 'required']
        ];
    }

    public function preview(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包不存在");
            }

            $detail = GiftpacksDetailForm::detail($giftpacks);

            $user = User::findOne(\Yii::$app->user->id);
            if(!$user || $user->is_delete){
                throw new \Exception("用户不存在");
            }

            $integralDeductionPrice = GiftpacksDetailForm::integralDeductionPrice($giftpacks, $user);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'detail' => $detail,
                    'balance' => $user->balance,
                    'integrals' => (float)$user->static_integral,
                    'integral_deduction_price' => $integralDeductionPrice
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