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
    public function get($path = "pages/index/index", $stands_mall_id = 0, $sid = 0)
    {

        $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['share'];
        $options = AppConfigLogic::getPosterConfig($stands_mall_id);
        $option = $options["share"];
        $option = $this->optionDiff($option, $default);
        if(empty($option['name']['text'])){
            isset($option['name']) && $option['name']['text'] = \Yii::$app->user->identity->nickname;
        }
        $cache = $this->getCache($option);
        if ($cache) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $cache . '?v=' . time()]);
        }
        \Yii::warning("appPlatform result:".json_encode(\Yii::$app->appPlatform));
        if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
            $file = $this->qrcode($option, [
                ['pid' => \Yii::$app->user->id,'mall_id'=>\Yii::$app->mall->id, 'sid' => $sid],//'source'=>User::SOURCE_SHARE_POSTER,
                280,
                $path
            ], $this);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this);
        }else{
            $path = $this->h5Path();
            if ($stands_mall_id && $stands_mall_id != 5) {
                $path = empty($path) ? "/mirror/#/?mall_id=".\Yii::$app->mall->id."&pid=".\Yii::$app->user->id."&source=".User::SOURCE_SHARE_POSTER . "&stands_mall_id=" . $stands_mall_id : $path;
            } else {
                $path = empty($path) ? "/h5/#/?mall_id=".\Yii::$app->mall->id."&pid=".\Yii::$app->user->id."&source=".User::SOURCE_SHARE_POSTER . "&stands_mall_id=" . $stands_mall_id : $path;
            }
            \Yii::warning("pathData result:".$path);
            $dir = $this->h5Dir();
            $dir = empty($dir) ? 'share/' . $stands_mall_id ."_".\Yii::$app->user->id. '.jpg' : $dir;
            $file = CommonLogic::createQrcode($option,$this,$path,$dir);

            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this, "share/");
        }

        if (isset($option['name']['center']) && $option['name']['center'] == 2) {
            $pos = imagettfbbox(14,0, $this->font_path, trim($option['name']['text']));
            $str_width = $pos[2] - $pos[0];
            $option['name']['left'] = (750 - $str_width) / 2;
        }

        $editor = $this->getPoster($option);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功', ['pic_url' => $editor->qrcode_url . '?v=' . time()]);
    }

    protected function h5Path(){
        return null;
    }

    protected function h5Dir(){
        return null;
    }
}