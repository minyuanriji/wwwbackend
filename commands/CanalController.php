<?php
namespace app\commands;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\models\Wechat;
use app\plugins\mch\models\Mch;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\Fmt;
use yii\console\Controller;
use Com\Alibaba\Otter\Canal\Protocol\Entry;

class CanalController extends Controller
{

    public function actionIndex()
    {
        try {
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);
            # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

            $client->connect("81.71.7.222", 11111);
            $client->checkValid();

            if(defined("ENV") && ENV == "pro"){
                $client->subscribe("1001", "example", "myrj.*");
            }else{
                $client->subscribe("1001", "example", "dev_myrj.*");
            }

            # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
                        $rowChange = new RowChange();
                        $rowChange->mergeFromString($entry->getStoreValue());
                        $evenType = $rowChange->getEventType();
                        $header = $entry->getHeader();

                        $tableName = $header->getTableName();
                        $tablePrefix = \Yii::$app->getDb()->tablePrefix;

                        if(empty($tableName))
                            continue;

                        $parts = explode("_", str_replace("-", "_", str_replace($tablePrefix, "", $tableName)));
                        $className = "";
                        foreach($parts as $part){
                            $className .= ucfirst($part);
                        }

                        $tableClassName = "\\app\\canal\\table\\{$className}";
                        if(!class_exists($tableClassName))
                            continue;

                        $tableClass = new $tableClassName();

                        $changeRows = [];
                        if(in_array($evenType, [EventType::UPDATE, EventType::INSERT])){

                            foreach ($rowChange->getRowDatas() as $rowData) {
                                $changeRow = [];
                                $afterColumns = $rowData->getAfterColumns();
                                foreach ($afterColumns as $column) {
                                    $changeRow[$column->getName()] = $column->getValue();
                                }
                                $changeRows[] = $changeRow;
                            }
                            if($evenType == EventType::INSERT){
                                if(method_exists($tableClass, "insert")){
                                    $tableClass->insert($changeRows);
                                }
                            }
                        }

                        if($evenType == EventType::UPDATE){
                            $condDatas = [];
                            foreach ($rowChange->getRowDatas() as $rowData) {
                                $beforeColumns = $rowData->getBeforeColumns();
                                $condData = [];
                                foreach ($beforeColumns as $column) {
                                    $condData[$column->getName()] = $column->getValue();
                                }
                                $condDatas[] = $condData;
                            }
                            $mixDatas = [];
                            foreach($condDatas as $index => $condData){
                                $mixDatas[] = [
                                    'condition' => $condData,
                                    'update' => $changeRows[$index]
                                ];
                            }
                            if(method_exists($tableClass, "update")){
                                $tableClass->update($mixDatas);
                            }
                        }

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
