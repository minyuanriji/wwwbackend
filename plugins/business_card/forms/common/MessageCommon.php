<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片插件-消息中心公共类
 * Author: zal
 * Date: 2020-07-25
 * Time: 10:10
 */

namespace app\plugins\business_card\forms\common;

use app\core\ApiCode;
use app\core\jmessage\JmessageCore;
use app\logic\CommonLogic;
use app\models\BaseModel;

class MessageCommon extends BaseModel
{
    public function createSignature(){
        /** @var JmessageCore $jm */
        $jm = \Yii::$app->jm;
        $appKey = $jm->appKey;
        $masterSecret = $jm->masterSecret;
        $randomStr = \Yii::$app->security->generateRandomString();
        $timestamp = CommonLogic::msectime();
        $signature = md5("appkey={$appKey}&timestamp={$timestamp}&random_str={$randomStr}&key={$masterSecret}");
        $returnData = [];
        $returnData["app_key"] = $appKey;
        $returnData["random_str"] = $randomStr;
        $returnData["timestamp"] = $timestamp;
        $returnData["signature"] = $signature;
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"ok",$returnData);
    }
}

