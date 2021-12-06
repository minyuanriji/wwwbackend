<?php
namespace app\commands;


use app\helpers\tencent_cloud\TencentCloudAudioHelper;
use app\plugins\mch\models\MchAdminUser;
use app\plugins\mch\models\MchMessage;

class WsClientController extends BaseCommandController {

    public function actionListen(){
        while (true){

            $mchMessage = MchMessage::find()->where([
                "type"   => "paid_notify_voice",
                "status" => 0
            ])->orderBy("updated_at ASC")->one();

            if(!$mchMessage) continue;

            $mchMessage->updated_at = time();
            $mchMessage->save();

            $mchMessage->try_count += 1;
            $mchMessage->status = 1;

            try {

                //获取客户端TOKEN
                $adminUser = MchAdminUser::findOne($mchMessage->admin_user_id);
                if($adminUser && $adminUser->access_token){
                    $cache = \Yii::$app->getCache();
                    $cachekey = "WsClientController:audio" . md5($mchMessage->content);
                    $base64Data = $cache->get($cachekey);
                    if(!$base64Data){
                        $base64Data = TencentCloudAudioHelper::request($mchMessage->content);
                        $cache->set($cachekey, $base64Data);
                    }

                    \Swoole\Coroutine\run(function () use($base64Data, $adminUser, $mchMessage){
                        $cli = new \Swoole\Coroutine\Http\Client('127.0.0.1', 9515);
                        $cli->post('/', [
                            "action"        => "MchPaidNotify",
                            "notify_mobile" => $adminUser->access_token,
                            "notify_data"   => "PAID:" . json_encode([
                                "text"       => $mchMessage->content,
                                "base64Data" => $base64Data,
                                "url"        => ""
                            ])
                        ]);
                        $cli->close();
                        if($cli->body != "SUCCESS") {
                            $mchMessage->status = 0;
                            $mchMessage->fail_reason = $cli->body;
                        }
                    });
                }

                if($mchMessage->try_count > 3) {
                    $mchMessage->status = 1;
                }
                if(!$mchMessage->save()){
                    throw new \Exception(json_encode($mchMessage->getErrors()));
                }

                $this->commandOut("通知商户[ID:{$mchMessage->mch_id}]付款成功");

            }catch (\Exception $e){
                $this->commandOut($e->getMessage());
                if($mchMessage->try_count > 3){
                    $mchMessage->status = 1;
                    $this->commandOut("通知商户[ID:{$mchMessage->mch_id}]付款失败。" . $e->getMessage());
                }
                $mchMessage->fail_reason = $e->getMessage();
                $mchMessage->save();
            }
            $this->sleep(3);
        }
    }

}

