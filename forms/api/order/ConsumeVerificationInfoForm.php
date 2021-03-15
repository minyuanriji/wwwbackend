<?php
namespace app\forms\api\order;

use app\core\ApiCode;
use app\forms\common\QrCodeCommon;
use app\helpers\ArrayHelper;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderGoodsConsumeVerification;
use app\models\User;
use app\plugins\mch\models\Mch;

class ConsumeVerificationInfoForm extends BaseModel{

    public $id;
    public $route;
    public $code;

    public function rules(){
        return [
            [['id'], 'integer'],
            [['route', 'code'], 'string'],
        ];
    }

    /**
     * 获取到店消费核销信息
     * @return array
     */
    public function info(){

        list($verificationLog, $order, $orderDetail) = $this->getData();

        if($verificationLog->user_id != \Yii::$app->user->id){
            throw new \Exception('无权限操作');
        }

        $returnData = ArrayHelper::toArray($verificationLog);

        $orderDetail = ArrayHelper::toArray($orderDetail);
        $returnData['goods_info'] = json_decode($orderDetail['goods_info'], true);

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"", $returnData);
    }

    /**
     * 确认使用核销二维码
     * @return array
     */
    public function useConfirm(){

        try {
            if (empty($this->code)) {
                throw new \Exception('核销码不能为空');
            }

            list($verificationLog, $order, $orderDetail) = $this->getData();

            if($verificationLog->is_used){
                throw new \Exception('订单信息已失效');
            }

            //获取当前登录的账号关联商户信息
            $mch = Mch::findOne([
                'user_id'       => \Yii::$app->user->id,
                'review_status' => Mch::REVIEW_STATUS_CHECKED,
                'is_delete'     => 0
            ]);
            if(!$mch){
                throw new \Exception('操作商户不存在');
            }

            if($verificationLog->mch_id != $mch->id){
                throw new \Exception('无权限操作');
            }

            $order->status = Order::SALE_STATUS_FINISHED;
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            $verificationLog->updated_at = time();
            $verificationLog->is_used = OrderGoodsConsumeVerification::STATUS_USED;
            if(!$verificationLog->save()){
                throw new \Exception($this->responseErrorMsg($verificationLog));
            }

            $detail = ArrayHelper::toArray($orderDetail);
            $detail['goods_info'] = json_decode($detail['goods_info'], true);

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"核销成功", [
                'detail' => $detail
            ]);
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

    /**
     * 到店消费核销二维码
     * @return array
     */
    public function qrCode(){

        try {
            if (empty($this->id)) {
                throw new \Exception('ID不能为空');
            }

            list($verificationLog, $order, $orderDetail) = $this->getData();

            if($verificationLog->user_id != \Yii::$app->user->id){
                throw new \Exception('无权限操作');
            }

            if(empty($this->route)){
                throw new \Exception('路由地址不能为空');
            }

            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $qrCode = new QrCodeCommon();
                $res = $qrCode->getQrCode([], 100, $this->route . "?code=" . $verificationLog->verification_code);
                $codeUrl = $res['file_path'];
            }else{
                $dir = 'clerk/' . \Yii::$app->mall->id . "/" . $this->id . '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                $file = CommonLogic::createQrcode([], $this, $this->route . "?code=" . $verificationLog->verification_code, $dir);
                $codeUrl = CommonLogic::uploadImgToCloudStorage($file, $dir, $imgUrl);
            }

            $detail = ArrayHelper::toArray($orderDetail);
            $detail['goods_info'] = json_decode($detail['goods_info'], true);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'url'    => $codeUrl,
                    'code'   => $verificationLog->verification_code,
                    'detail' => $detail
                ]
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

    private function getData(){
        $where = [
            "is_delete" => 0
        ];
        if(empty($this->id)){
            $where['verification_code'] = $this->code;
        }else{
            $where['id'] = $this->id;
        }
        $verificationLog = OrderGoodsConsumeVerification::findOne($where);
        if(!$verificationLog){
            throw new \Exception('记录不存在或无效');
        }

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

        return [ $verificationLog, $order, $orderDetail];
    }
}