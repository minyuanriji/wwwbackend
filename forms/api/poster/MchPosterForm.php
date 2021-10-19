<?php

namespace app\forms\api\poster;

use app\core\ApiCode;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\forms\common\QrCodeCommon;
use app\logic\CommonLogic;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;

class MchPosterForm extends GrafikaOption implements BasePoster
{
    public $mch_id;
    public $route;

    use CustomizeFunction;
    public function get()
    {
        try {
            //获取商户
            $mchModel = Mch::findOne([
                'id'            => $this->mch_id,
                'review_status' => Mch::REVIEW_STATUS_CHECKED,
                'is_delete'     => 0
            ]);
            if (!$mchModel)
                throw new \Exception('商户不存在');

            $store = Store::findOne(['mch_id' => $mchModel->id]);
            if (!$store)
                throw new \Exception('商户不存在');

            $pid = $mchModel->user_id;
            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $qrCode = new QrCodeCommon();
                $res = $qrCode->getQrCode(['id' => $mchModel->id, 'pid' => $pid], 300, $this->route);
                $codeUrl = $res['file_path'];
            }else{
                $dir = "mch/checkout-order-qrcode/" . $mchModel->id . time() . '.jpg';
                $imgUrl = \Yii::$app->request->hostInfo . "/runtime/image/" . $dir;
                CommonLogic::createQrcode([], $this, $this->route . "?id=" . $mchModel->id . "&pid=" . $pid, $dir);
                $codeUrl = $imgUrl;
            }
            $pos = imagettfbbox(14,0, $this->font_path, $store->name);
            $str_width = $pos[2] - $pos[0];
            $option = [
                'bg_pic' => [
                    'url' => \Yii::$app->basePath . '\runtime\image\bj.jpg',
                    'is_show' => 1,
                ],
                'qr_code' => [
                    'is_show' => 1,
                    'size' => 300,
                    'top' => 10,
                    'left' => 10,
                    'type' => 1,
                    'file_type' => 'image',
                    'file_path' => $codeUrl,
                ],
                'name' => [
                    'is_show' => 1,
                    'font' => 14,
                    'top' => 320,
                    'left' => (320 - $str_width) / 2,
                    'color' => '#000',
                    'file_type' => 'text',
                    'text' => $store->name,
                ],
            ];

            $qrCodeUrl = $this->setFile($option);
            if (!$qrCodeUrl) {
                $editor = $this->getPoster($option, 320, 352);
                $qrCodeUrl = $editor->qrcode_url;
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' =>  $qrCodeUrl . '?v=' . time()]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}