<?php


namespace app\mch\forms\api;


use app\core\ApiCode;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\Mch;

class CheckoutOrderQrcodeForm extends BaseModel {

    public $route;

    public function rules(){
        return [
            [['route'], 'required'],
            [['route'], 'string']
        ];
    }

    public function getQrcode(){

        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        try {
            //获取商户
            $mchModel = Mch::findOne([
                'user_id'       => \Yii::$app->user->id,
                'review_status' => Mch::REVIEW_STATUS_CHECKED,
                'is_delete'     => 0
            ]);
            if (!$mchModel) {
                throw new \Exception('商户不存在');
            }

            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $qrCode = new QrCodeCommon();
                $res = $qrCode->getQrCode([], 100, $this->route . "?id=" . $mchModel->id);
                $codeUrl = $res['file_path'];
            }else{
                $dir = "mch/checkout-order-qrcode/" . $mchModel->id . '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                $file = CommonLogic::createQrcode([], $this, $this->route . "?id=" . $mchModel->id, $dir);
                //$codeUrl = CommonLogic::uploadImgToCloudStorage($file, $dir, $imgUrl);
                $codeUrl = $imgUrl;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", [
                'qrcode' => $codeUrl
            ]);
        }catch (\Exception $e){
            \Yii::$app->redis->set('var1',$e -> getMessage());
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }

    }

}