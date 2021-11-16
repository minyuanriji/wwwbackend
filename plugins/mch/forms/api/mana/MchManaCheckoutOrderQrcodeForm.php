<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\controllers\api\mana\MchAdminController;

class MchManaCheckoutOrderQrcodeForm extends BaseModel {

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
            $pid = MchAdminController::$adminUser['mch']['user_id'];
            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $qrCode = new QrCodeCommon();
                $res = $qrCode->getQrCode(['id' => MchAdminController::$adminUser['mch_id'], 'pid' => $pid], 300, $this->route);
                $codeUrl = $res['file_path'];
            }else{
                //$this->route = "/h5/#/mch/personalCentre/ercode/payPages/payPages";
                $dir = "mch/checkout-order-qrcode/" . MchAdminController::$adminUser['mch_id'] . time() . '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                $file = CommonLogic::createQrcode([], $this, $this->route . "?id=" . MchAdminController::$adminUser['mch_id'] . "&pid=" . $pid, $dir);
                $codeUrl = $imgUrl;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", [
                'qrcode' => $codeUrl
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }
}