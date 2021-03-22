<?php
namespace app\plugins\mch\controllers\mall;

use app\core\ApiCode;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\sign_in\forms\BaseModel;

class CheckoutOrderDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return array_merge(parent::rules(), [
            [["id"], "integer"]
        ]);
    }

    public function getDetail() {
        try {
            $detail = MchCheckoutOrder::find()->where([
                'id'        => $this->id,
                'mall_id'   => \Yii::$app->mall->id,
                'is_delete' => 0,
            ])->with('mchStore', 'payUser')->asArray()->one();
            if (!$detail) {
                throw new \Exception('订单不存在');
            }

            $detail['format_pay_time'] = "";
            if($detail['is_pay']){
                $detail['format_pay_time'] = date("Y-m-d H:i:s", $detail['pay_at']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'detail' => $detail,
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }



    }
}