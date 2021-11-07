<?php

namespace app\plugins\giftpacks\forms\api;

use app\core\ApiCode;
use app\forms\api\poster\BasePoster;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\logic\AppConfigLogic;
use app\logic\CommonLogic;
use app\models\Goods;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;

class GiftpacksGroupDetailShareForm extends GrafikaOption implements BasePoster{

    use CustomizeFunction;

    public $pack_id;
    public $group_id;

    public function rules(){
        return [
            [['pack_id', 'group_id'], 'required']
        ];
    }

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $giftpacks = Giftpacks::findOne($this->pack_id);
            if(!$giftpacks || $giftpacks->is_delete){
                throw new \Exception("大礼包[ID:{$this->pack_id}]不存在");
            }

            $group = GiftpacksGroup::findOne($this->group_id);
            if(!$group){
                throw new \Exception("拼单信息不存在");
            }

            $default = (new \app\forms\mall\poster\PosterForm())->getDefault()['goods'];
            $options = AppConfigLogic::getPosterConfig();
            $option = $options["goods"];
            $option = $this->optionDiff($option, $default);

            isset($option['pic']) && $option['pic']['file_path'] = $giftpacks->cover_pic;
            isset($option['desc']) && $option['desc']['text'] = self::autowrap($option['desc']['font'], 0, $this->font_path, $option['desc']['text'], $option['desc']['width']);
            isset($option['name']) && $option['name']['text'] = self::autowrap($option['name']['font'], 0, $this->font_path, $giftpacks->title, 750 - (float)$option['name']['left'] - 40, 2);
            isset($option['nickname']) && $option['nickname']['text'] = \Yii::$app->user->identity->nickname;

            $option['price']['text'] = "￥" . $giftpacks->price;

            $cache = $this->getCache($option);
            if ($cache) {
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $cache . '?v=' . time()]);
            }

            $user_id = \Yii::$app->user->id;

            if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
                $file = $this->qrcode($option, [
                    ['pack_id' =>  $this->pack_id, 'group_id' => $this->group_id, 'type' => 1, 'pid' => \Yii::$app->user->id,'source'=>User::SOURCE_SHARE_GIFTPACKS,'mall_id'=>\Yii::$app->mall->id],
                    280,
                    'mch/giftbag/spelldetail/spelldetail'
                ], $this);
                isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
                isset($option['head']) && $option['head']['file_path'] = self::head($this);
            }else{
                $path = "/h5/#/mch/giftbag/spelldetail/spelldetail?mall_id=".\Yii::$app->mall->id."&pack_id=".$this->pack_id."&group_id=".$this->group_id."&type=1&pid=".$user_id."&source=".User::SOURCE_SHARE_GIFTPACKS;;
                $dir = 'giftpacks/' . $this->pack_id . time() . uniqid() . '.jpg';
                $file = CommonLogic::createQrcode($option,$this,$path,$dir);
                isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
                isset($option['head']) && $option['head']['file_path'] = self::head($this, "giftpacks/");
            }
            $editor = $this->getPoster($option);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $editor->qrcode_url . '?v=' . time()]);
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