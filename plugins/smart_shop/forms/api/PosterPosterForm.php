<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\helpers\CloudStorageHelper;
use app\logic\AppConfigLogic;
use GuzzleHttp\Client;

class PosterPosterForm extends GrafikaOption implements BasePoster{

    use CustomizeFunction;

    public $qrcode;
    public $from;
    public $data;

    private $cacheKey;

    public function rules() {
        return [
            [['qrcode', 'from', 'data'], 'required']
        ];
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $this->cacheKey = md5($this->qrcode . $this->from . $this->data);
            $picUrl = \Yii::$app->cache->get($this->cacheKey);
            $isCache = true;
            if(!$picUrl || true){
                $isCache = false;
                $data = json_decode($this->data, true);

                $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['goods'];
                $options = AppConfigLogic::getPosterConfig();
                $option = $options["goods"];
                $option = $this->optionDiff($option, $default);
                $this->setFile($option);

                unset($option['desc']);

                //设置头像
                if(!isset($data['avatar']) || empty($data['avatar'])){
                    $avatar = ROOT_PATH . "/statics/img/common/default-avatar.png";
                }else{
                    $avatar = $data['avatar'];
                    $this->saveLocalTempFile($avatar);
                }

                isset($option['head']) && $option['head']['file_path'] = $avatar;

                //设置昵称
                $nickName = $data['mobile'];
                if(isset($data['nickname']) && !empty($data['nickname'])){
                    $nickName = $data['nickname'];
                }

                isset($option['pic']) && $option['pic']['file_path'] = $data['goods_thumb'];
                isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $data['store_name'], $option['desc']['width']);
                isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $data['goods_name'], 750 - (float)$option['name']['left'] - 40, 2);
                isset($option['nickname']) && $option['nickname']['text'] = $nickName;

                if (isset($option['price'])) {
                    $option['price']['text'] = '￥' . $data['goods_price'];
                }

                if (isset($option['price']) && isset($option['name'])) {
                    //自适应
                    $nameSize = imagettfbbox($option['name']['font'], 0, $this->font_path, $option['name']['text']);
                    $nameHeight = $option['name']['top'] + $nameSize[1] - $nameSize[7];

                    $priceSize = imagettfbbox($option['price']['font'], 0, $this->font_path, $option['price']['text']);
                    $priceHeight = $option['price']['top'] + $priceSize[1] - $priceSize[7];

                    //compare
                    if ($nameHeight > $option['price']['top'] && $priceHeight > $option['name']['top']) {
                        $option['price']['top'] = $nameHeight + 25;
                    }
                }

                $option['head']['size'] = 55;

                //计算出昵称宽度
                $nicknameSize = imagettfbbox($option['nickname']['font'], 0, $this->font_path, $option['nickname']['text']);
                $nicknameWidth = abs($nicknameSize[2] - $nicknameSize[0]);
                $nicknameHeight = abs($nicknameSize[5] - $nicknameSize[3]);

                //昵称加头像的宽度
                $nickAvatarSize = $nicknameWidth + $option['head']['size'];

                //调整二维码位置
                $option['qr_code']['left'] = ($option['pic']['width'] - $option['qr_code']['size'])/2;
                $option['qr_code']['top'] = $option['price']['top'] + 60;

                //调整头像位置
                $option['head']['top'] = $option['qr_code']['top'] + $option['qr_code']['size'] + 20;
                $option['head']['left'] = $option['qr_code']['left'] + ($option['qr_code']['size'] - $nickAvatarSize)/2;

                //调整昵称位置
                $option['nickname']['top'] = $option['head']['top'] + ($option['head']['size'] - $nicknameHeight)/2;
                $option['nickname']['left'] = $option['head']['left'] + $option['head']['size'];

                //远程文件临时存储到本地
                //$this->saveLocalTempFile($data['store_logo']);
                //$this->saveLocalTempFile($data['goods_thumb']);
                $this->saveLocalTempFile($this->qrcode);

                isset($option['qr_code']) && $option['qr_code']['file_path'] = $this->qrcode;

                $editor = $this->getPoster($option);
                $picUrl = $editor->qrcode_url;

                \Yii::$app->cache->set($this->cacheKey, $picUrl, 1800);
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '请求成功', [
                'is_cache' => $isCache ? 1 : 0,
                'pic_url'  => CloudStorageHelper::url($picUrl) . '?v=' . time(),
                'pic_width' => 321,
                'pic_height' => 564
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), [
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
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