<?php


namespace app\forms\api\user;

use app\controllers\business\Poster;
use app\core\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\grafika\GrafikaOption;
use app\forms\common\QrCodeCommon;
use app\models\User;

class UserLinkPosterNewForm extends GrafikaOption implements BasePoster{

    public $flag;

    public function rules(){
        return [
            [['flag'], 'safe']
        ];
    }

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $code = (new QrCodeCommon())->getQrCode(['pid' => \Yii::$app->user->id], 350, 'pages/index/index');
            }

            $config = array(
                'bg_url' => \Yii::$app->basePath . '/web/statics/bg/redpack.png',//背景图片路径
                'text' => [
                    [
                        'text' => '',//扫码领红包
                        'left' => 116,
                        'top' => 280,
                        'width' => 300,
                        'fontSize' => 14, //字号
                        'fontColor' => '255,255,255', //字体颜色
                        'angle' => 0,
                    ]
                ],
                'image' => [
                    [
                        'name' => '二维码', //图片名称，用于出错时定位
                        'url' => '',
                        'stream' => file_get_contents($code['file_path']),
                        'left' => 190,
                        'top' => 390,
                        'right' => 0,
                        'bottom' => 0,
                        'width' => 380,
                        'height' => 380,
                        'radius' => 0,
                        'opacity' => 100
                    ]
                ]
            );
            Poster::setConfig($config);

            //设置保存路径
            $fileName =  "/web/temp/" . md5(uniqid()) . ".jpg";
            if(!Poster::make(\Yii::$app->basePath . $fileName)){
                throw new \Exception("分享海报生成失败");
            }

            //是否要清理缓存资源
            Poster::clear();

            return [
                'status' => 1,
                'img'    => \Yii::$app->request->hostInfo . $fileName
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

}