<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-20
 * Time: 16:40
 */

namespace app\forms\mall\wechat;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Wechat;

class WechatForm extends BaseModel
{
    public function search()
    {
        $wechat = Wechat::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0]);
        if (!$wechat) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'=>'还未配置公众号'
            ];
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'wechat' => $wechat
            ]
        ];
    }
}