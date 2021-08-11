<?php


namespace app\forms\api\user;

use app\core\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\models\User;

class UserLinkPosterNewForm extends GrafikaOption implements BasePoster{

    public $flag;

    use CustomizeFunction;

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

            $shareUrl = \Yii::$app->request->hostInfo . '/h5/#/pages/index/index?user_id='. \Yii::$app->user->id . '&pid=' . \Yii::$app->user->id;

            $option['qr_code'] = [
                "is_show" => 1,
                "size" => 240,
                "top" => 1030,
                "left" => 460,
                "type" => 1,
                "file_type" => "image"
            ];

            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $file = $this->qrcode($option, [
                    [
                        'pid' => \Yii::$app->user->id
                    ],
                    280,
                    'pages/index/index'
                ], $this);
            }

            $option = array_merge($option, [
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
                        'stream' => file_get_contents($file),
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
            ]);

            $editor = $this->getPoster($option);

            echo $editor->qrcode_url . '?v=' . time();
            exit;

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