<?php

namespace app\plugins\diy\forms\api;

use app\core\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\User;
use GuzzleHttp\Client;

class DiyPagePosterForm extends GrafikaOption implements BasePoster{

    use CustomizeFunction;
    public $page_id;
    public $pic_url;

    public function rules() {
        return [
            [['pic_url'], 'trim'],
            [['page_id'], 'integer'],
        ];
    }

    public function get(){

        if(\Yii::$app->user->isGuest){
            return $this->returnApiResultData(ApiCode::CODE_NOT_LOGIN, '请先登录');
        }

        $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['share'];
        $options = AppConfigLogic::getPosterConfig();
        $option = $options["share"];
        $option = $this->optionDiff($option, $default);
        if(empty($option['name']['text'])){
            isset($option['name']) && $option['name']['text'] = \Yii::$app->user->identity->nickname;
        }
        if($this->pic_url){
            $option['bg_pic']['url'] = $this->pic_url;
        }

        $cache = $this->getCache($option);
        if ($cache) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $cache . '?v=' . time()]);
        }
        if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
            $file = $this->qrcode($option, [
                ['pid' => \Yii::$app->user->id, 'mall_id'=>\Yii::$app->mall->id, 'page_id' => $this->page_id],
                280,
                "pages/diy/diy"
            ], $this);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this);
        }else{
            $path = "/h5/#/pages/diy/diy?mall_id=" . \Yii::$app->mall->id . "&pid=".\Yii::$app->user->id."&source=".User::SOURCE_SHARE_POSTER;
            $dir = 'share/' . \Yii::$app->user->id . '/diy_page_' . $this->page_id . '.jpg';
            $file = CommonLogic::createQrcode($option, $this, $path, $dir);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this, "share/");
        }

        $option['qr_code']['top'] -= $option['qr_code']['size'];
        $option['qr_code']['left'] = (750 - $option['qr_code']['size']) / 2;

        $pos = imagettfbbox(14,0, $this->font_path, trim($option['name']['text']));
        $str_width = $pos[2] - $pos[0];
        $option['head']['left'] = (750 - ($option['head']['size'] + $str_width)) / 2 - 30;
        $option['name']['left'] = $option['head']['left'] + $option['head']['size'] + 10;
        $option['head']['top'] += 70;
        $option['name']['top'] += 60;

        $editor = $this->getPoster($option);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功', ['pic_url' => $editor->qrcode_url . '?v=' . time()]);
    }

    protected function h5Path(){
        return null;
    }

    protected function h5Dir(){
        return null;
    }

    /**
     * @param $file
     * @throws \Exception
     */
    private function saveLocalTempFile(&$file){

        $tempFile = \Yii::$app->runtimePath . '/image/temp/' . md5($file) . '.jpg';
        !is_dir(dirname($tempFile)) && mkdir(dirname($tempFile), 0755, true);

        $client = new Client(['verify' => false]);
        $response = $client->get($file, ['save_to' => $tempFile]);
        if($response->getStatusCode() == 200) {
            $file = $tempFile;
        } else {
            throw new \Exception('临时文件存储失败');
        }
    }
}