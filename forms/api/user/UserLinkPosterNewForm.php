<?php


namespace app\forms\api\user;

use app\core\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\logic\AppConfigLogic;
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

            $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['share'];
            $options = AppConfigLogic::getPosterConfig();
            $option = $options["share"];
            $option = $this->optionDiff($option, $default);

            $option['bg_pic']['url'] = \Yii::$app->basePath . "/web/statics/bg/redpack.png";

            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $file = $this->qrcode($option, [['pid' => \Yii::$app->user->id], 280, 'pages/index/index' ], $this);
            }

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