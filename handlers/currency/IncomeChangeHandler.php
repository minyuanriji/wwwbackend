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
use app\helpers\ArrayHelper;
use app\models\ErrorLog;
use app\models\IncomeLog;
use app\services\wechat\WechatTemplateService;
use app\handlers\BaseHandler;

class IncomeChangeHandler extends BaseHandler
{
    public $notice_flag=[
        IncomeLog::FLAG_SETTLEMENT,IncomeLog::FLAG_REFUND,IncomeLog::FLAG_CASH
    ];

    public function register()
    {
        \Yii::$app->on(IncomeLog::EVENT_INCOME_CHANGE,function ($event){
            //income有bug,先不处理
//            //冻结状态不发通知
//            if (in_array($event->income_log->flag,$this->notice_flag)) {
//                $this->sendWechatTemp($event->income_log);
//            }
        });
    }

    public function sendWechatTemp(IncomeLog $income_Log){
        $WechatTemplateService = new WechatTemplateService($income_Log->mall_id);

        $url = "/plugins/extensions/index";

        $h5_url = \Yii::$app->params['web_url'] . "/h5/#" . $url;

        $platform   = $WechatTemplateService->getPlatForm();

        if ($income_Log->type == IncomeLog::TYPE_IN) {
            $keyword = "收益增加:" . $income_Log->money."元";
        } else {
            $keyword = "收益减少:" . $income_Log->money."元";
        }

        $send_data = [
            'first'    => '尊敬的用户,您的收益余额发生变化,时间:' . date("Y-m-d H:i:s", time()),
            'keyword1' => $keyword,
            'keyword2' => $income_Log->income."元",
            'remark'   => $income_Log->desc
        ];

        return $WechatTemplateService->send($income_Log->user_id, WechatTemplateService::TEM_KEY['income_change']['tem_key'], $h5_url, $send_data, $platform, $url);
    }
}