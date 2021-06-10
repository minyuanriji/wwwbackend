<?php
namespace app\commands;

use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\Fmt;
use yii\console\Controller;
use Com\Alibaba\Otter\Canal\Protocol\Entry;

class HelloController extends Controller
{

    public function actionIndex()
    {
        try {
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
            # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

            $client->connect("81.71.7.222", 11111);
            $client->checkValid();
            $client->subscribe("1001", "example", "myrj.jxmall_plugin_mch");
            # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
                        Fmt::println($entry);
                    }
                }
                sleep(1);
            }

            $client->disConnect();
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }

    }

}
