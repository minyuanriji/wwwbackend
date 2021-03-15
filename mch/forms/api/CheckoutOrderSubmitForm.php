<?php


namespace app\mch\forms\api;


use app\core\ApiCode;
use app\forms\common\QrCodeCommon;
use app\helpers\ArrayHelper;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\Order;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;

class CheckoutOrderSubmitForm extends BaseModel {

    public $order_price;
    public $route;

    public function rules(){
        return [
            [['order_price', 'route'], 'required'],
            [['order_price'], 'number'],
            [['route'], 'string']
        ];
    }

    public function create(){
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {

            //获取商户
            $mchModel = Mch::findOne([
                'user_id'       => \Yii::$app->user->id,
                'review_status' => 1,
                'is_delete'     => 0
            ]);
            if(!$mchModel){
                throw new \Exception('商户不存在');
            }

            //判断收款金额
            if(empty($this->order_price) || $this->order_price <= 0){
                throw new \Exception('收款金额必须大于0');
            }

            //判断是否有未支付的订单
            //有就复用订单
            $order = MchCheckoutOrder::find()->where([
                'is_pay'  => 0,
                'mall_id' => $mchModel->mall_id,
                'mch_id'  => $mchModel->id
            ])->one();
            if(!$order){
                $order = new MchCheckoutOrder();
                $order->mall_id  = \Yii::$app->mall->id;
                $order->mch_id   = $mchModel->id;
                $order->order_no = Order::getOrderNo('MS');
            }

            $order->order_price              = $this->order_price;
            $order->pay_price                = 0;
            $order->is_pay                   = 0;
            $order->pay_user_id              = 0;
            $order->pay_at                   = 0;
            $order->score_deduction_price    = 0;
            $order->integral_deduction_price = 0;
            $order->created_at               = time();
            $order->updated_at               = time();
            $order->is_delete                = 0;
            if (!$order->save()) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL,(new BaseModel())->responseErrorMsg($order));
            }

            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $qrCode = new QrCodeCommon();
<<<<<<< HEAD
                $res = $qrCode->getQrCode(['id' => $order->id], 100, $this->route);
                $codeUrl = $res['file_path'];
            }else{
                $dir = "checkout-order/" . $mchModel->id . "/" . $order->id. '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                $file = CommonLogic::createQrcode(['id' => $order->id], $this, $this->route, $dir);
=======
                $res = $qrCode->getQrCode([], 100, $this->route . "?id=" . $order->id);
                $codeUrl = $res['file_path'];
            }else{
                $dir = "checkout-order/" . $mchModel->id . "/" . $order->mch_id . "/" . $order->id . time(). '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                $file = CommonLogic::createQrcode([], $this, $this->route . "?id=" . $order->id, $dir);
>>>>>>> 3e6bcd95c340635d2b5e49428acc3a563617fd7c
                $codeUrl = CommonLogic::uploadImgToCloudStorage($file, $dir, $imgUrl);
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", [
                'order'  => ArrayHelper::toArray($order),
                'qrcode' => $codeUrl
            ]);
        }catch(\Exception $e){
            \Yii::$app->redis->set('var1',$e -> getMessage());
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }
}