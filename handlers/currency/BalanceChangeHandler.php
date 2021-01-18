<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: xuyaoxiang
 * Date: 2020-10-13
 * Time: 12:58
 */

namespace app\handlers\currency;
use app\models\BalanceLog;
use app\services\wechat\WechatTemplateService;
use app\handlers\BaseHandler;

class BalanceChangeHandler extends BaseHandler
{
    public function register()
    {
        \Yii::$app->on(BalanceLog::EVENT_BALANCE_CHANGE,function ($event){
            $this->sendWechatTemp($event->balance_log);
        });
    }

    public function sendWechatTemp(BalanceLog $balance_log){
        $WechatTemplateService = new WechatTemplateService($balance_log->mall_id);

        $url = "/pages/user/balance/details";

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform   = $WechatTemplateService->getPlatForm();

        if ($balance_log->type == BalanceLog::TYPE_ADD) {
            $keyword = "增加:" . $balance_log->money;
        } else {
            $keyword = "消费:" . $balance_log->money;
        }

        $send_data = [
            'first'    => '尊敬的用户,您的账户余额发生变化,时间:' . date("Y-m-d H:i:s", time()),
            'keyword1' => $keyword,
            'keyword2' => $balance_log->balance,
            'remark'   => $balance_log->desc
        ];

        return $WechatTemplateService->send($balance_log->user_id, WechatTemplateService::TEM_KEY['balance_change']['tem_key'], $h5_url, $send_data, $platform, $url);
    }
}