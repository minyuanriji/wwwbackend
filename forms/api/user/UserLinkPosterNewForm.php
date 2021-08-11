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
                        'user_id' => \Yii::$app->user->id,
                        'pid'     => \Yii::$app->user->id,
                        'mall_id' => \Yii::$app->mall->id
                    ],
                    280,
                    'pages/index/index'
                ], $this);
                print_r($file);
                exit;
            }

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}