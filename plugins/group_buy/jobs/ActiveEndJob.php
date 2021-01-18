<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/18
 * Time: 16:43
 */

namespace app\plugins\group_buy\jobs;


use app\models\ErrorLog;
use app\plugins\group_buy\services\ActiveServices;
use yii\base\Component;
use yii\queue\JobInterface;
use app\services\wechat\WechatParmasService;

class ActiveEndJob extends Component implements JobInterface
{
    public $active_id;
    public $mall; //商城

    public function execute($queue)
    {
        \Yii::$app->setMall($this->mall);

        $WechatParmasService = new WechatParmasService();
        $WechatParmasService->setWechatParmas(\Yii::$app->mall->id);

        try {
            $ActiveServices = new ActiveServices();
            $return         = $ActiveServices->timeEnd($this->active_id);

            if (!isset($return['code'])) {
                throw new \Exception(json_encode($return));
            }

            if ($return['code'] > 0) {
                throw new \Exception(json_encode($return['msg'], $return['code']));
            }

        } catch (\Exception $e) {
            //错误日志
            $error = "file:" . $e->getFile() . ";Line:" . $e->getLine() . ";message:" . $e->getMessage().";code:".$e->getCode();
            \Yii::error("ActiveEndJob:" . $error);
            $ErrorLog            = new ErrorLog();
            $ErrorLog->store("ActiveEndJob",$error);
        }
    }
}
