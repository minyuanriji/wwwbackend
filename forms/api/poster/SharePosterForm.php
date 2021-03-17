<?php

namespace app\forms\api\poster;

use app\core\ApiCode;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\User;

class SharePosterForm extends GrafikaOption implements BasePoster
{
    use CustomizeFunction;
    public function get($path="pages/index/index")
    {
        $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['share'];
        $options = AppConfigLogic::getPosterConfig();
        $option = $options["share"];
        $option = $this->optionDiff($option, $default);
        if(empty($option['name']['text'])){
            isset($option['name']) && $option['name']['text'] = \Yii::$app->user->identity->nickname;
        }
        $cache = $this->getCache($option);
        if ($cache) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $cache . '?v=' . time()]);
        }
        if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
            $file = $this->qrcode($option, [
                ['pid' => \Yii::$app->user->id,'source'=>User::SOURCE_SHARE_POSTER,'mall_id'=>\Yii::$app->mall->id],
                280,
                $path
            ], $this);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this);
        }else{
            $path = "/h5/#/?mall_id=".\Yii::$app->mall->id."&pid=".\Yii::$app->user->id."&source=".User::SOURCE_SHARE_POSTER;
            $dir = 'share/' . \Yii::$app->mall->id."_".\Yii::$app->user->id. '.jpg';
            $file = CommonLogic::createQrcode($option,$this,$path,$dir);

            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this,"share/");
        }

        $editor = $this->getPoster($option);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $editor->qrcode_url . '?v=' . time()]);
    }
}