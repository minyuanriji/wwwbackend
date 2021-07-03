<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: xuyaoxiang
 * Date: 2020-10-13
 * Time: 12:58
 * 积分变动事件处理
 */

namespace app\handlers\currency;
use app\models\ErrorLog;
use app\models\ScoreLog;
use app\services\wechat\WechatTemplateService;
use app\handlers\BaseHandler;

class ScoreChangeHandler extends BaseHandler
{
    public function register()
    {
        \Yii::$app->on(ScoreLog::EVENT_SCORE_CHANGE,function ($event){
            $this->sendWechatTemp($event->score_log);
        });
    }

    public function sendWechatTemp(ScoreLog $score_log)
    {
        $WechatTemplateService = new WechatTemplateService($score_log->mall_id);

        $url = "/pages/user/score/details";

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform = $WechatTemplateService->getPlatForm();

        if ($score_log->type == ScoreLog::TYPE_ADD) {
            $type = "增加";
        } else {
            $type = "抵扣";
        }

        $send_data = [
            'first'  => '尊敬的用户,您的积分发生变化',
            'time'   => date("Y-m-d H:i:s", time()),
            'type'   => $type,
            'Point'  => $score_log->score,
            'From'   => $score_log->desc,
            'remark' => "目前积分:" . $score_log->current_score
        ];

        return $WechatTemplateService->send($score_log->user_id, WechatTemplateService::TEM_KEY['score_change']['tem_key'], $h5_url, $send_data, $platform, $url);
    }
}