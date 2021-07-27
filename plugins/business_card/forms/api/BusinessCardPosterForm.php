<?php

namespace app\plugins\business_card\forms\api;

use app\core\ApiCode;
use app\forms\common\grafika\CustomizeFunction;
use app\forms\common\grafika\GrafikaOption;
use app\logic\CommonLogic;
use app\models\User;
use app\plugins\business_card\forms\common\Common;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardSetting;

class BusinessCardPosterForm extends GrafikaOption
{
    use CustomizeFunction;
    public function get()
    {
        $default = Common::getDefault()["business_card"];
        $options = Common::getBusinessCardPoster();
        $option = $options["business_card"];
        $option = $this->optionDiff($option, $default);

        isset($option['name']) && $option['name']['text'] = \Yii::$app->user->identity->nickname;
        $cache = $this->getCache($option);
        if ($cache) {
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $cache . '?v=' . time()]);
        }

        $userId = \Yii::$app->user->id;
        $params = [];
        $params["is_one"] = 1;
        $params["mall_id"] = \Yii::$app->mall->id;
        $params["user_id"] = $userId;
        $fields = ['id'];
        /** @var BusinessCard $businessCards */
        $businessCards = BusinessCard::getData($params,$fields);
        $id = 0;
        if(!empty($businessCards)){
            $id = $businessCards["id"];
        }

        if(\Yii::$app->appPlatform == User::PLATFORM_MP_WX){
            $file = $this->qrcode($option, [
                ['id' => $id, 'pid' => \Yii::$app->user->id,'source' => User::SOURCE_SHARE_CARD,'mall_id'=>\Yii::$app->mall->id],
                280,
                '/plugins/business-card/index'
            ], $this);
            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this);
        }else{
            $path = "/h5/#/plugins/business-card/index?mall_id=".\Yii::$app->mall->id."&pid=".\Yii::$app->user->id."&id=".$id."&source=".User::SOURCE_SHARE_CARD;
            $dir = 'business_card/' . \Yii::$app->mall->id."_".\Yii::$app->user->id. '.jpg';
            $file = CommonLogic::createQrcode($option,$this,$path,$dir);

            isset($option['qr_code']) && $option['qr_code']['file_path'] = $file;
            isset($option['head']) && $option['head']['file_path'] = self::head($this,"business_card/");
        }

        $editor = $this->getPoster($option);
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'请求成功',['pic_url' => $editor->qrcode_url . '?v=' . time()]);
    }
}