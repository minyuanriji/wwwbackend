<?php
namespace app\forms\api\order;

use app\core\ApiCode;
use app\forms\common\QrCodeCommon;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderGoodsConsumeVerification;

class ConsumeVerificationInfoForm extends BaseModel{

    public $id;
    public $route;

    public function rules(){
        return [
            [['id'], 'integer'],
            [['route'], 'string'],
            [['id'], 'required'],
        ];
    }

    public function checkVerificationLog($verificationLog){

        $order = $verificationLog->order;
        if(!$order || $order->is_delete){
            throw new \Exception('订单不存在');
        }

        if($order->is_pay != Order::IS_PAY_YES){
            throw new \Exception('订单未付款');
        }

        $orderDetail = $verificationLog->orderDetail;
        if(!$orderDetail || $orderDetail->is_delete){
            throw new \Exception('订单详情不存在');
        }

        if($orderDetail->is_refund && $orderDetail->refund_status != OrderDetail::REFUND_STATUS_SALES_END_REJECT){
            throw new \Exception('订单详情状态异常');
        }

    }

    /**
     * 到店消费核销二维码
     * @return array
     */
    public function qrCode(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $verificationLog = OrderGoodsConsumeVerification::findOne([
                "id"        => $this->id,
                "is_used"   => 0,
                "is_delete" => 0
            ]);
            if(!$verificationLog){
                throw new \Exception('记录不存在或无效');
            }

            $this->checkVerificationLog($verificationLog);

            $qrCode = new QrCodeCommon();

            if(empty($this->route)){
                throw new \Exception('路由地址不能为空');
            }
            $res = $qrCode->getQrCode(['id' => $this->id], 100, $this->route);
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => $res
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