<?php
namespace app\commands;

use app\models\Mall;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;

class CanalController extends BaseCommandController
{

    public function actionIndex()
    {
        $this->commandOut("CanalController start");

        \Yii::$app->setMall(Mall::findOne(5));

        try {
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET);
            # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

            $canalConf = \Yii::$app->params['canal'];
            $client->connect($canalConf['host'], $canalConf['port']);
            $client->checkValid();

            $client->subscribe($canalConf['subscribe']['clientId'], "example", $canalConf['subscribe']['filter']);

            # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

            $allowSchemas = array_keys($canalConf['allows']);

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
                        $rowChange = new RowChange();
                        $rowChange->mergeFromString($entry->getStoreValue());
                        $evenType = $rowChange->getEventType();
                        $header = $entry->getHeader();

                        if(!in_array($header->getSchemaName(), $allowSchemas)){
                            continue;
                        }

                        $schema = $header->getSchemaName();
                        $tableName = $header->getTableName();
                        $tablePrefix = \Yii::$app->getDb()->tablePrefix;

                        if(empty($tableName))
                            continue;

                        if($canalConf['allows'][$schema] == "smart_shop"){
                            $tablePrefix = "tplay_";
                        }

                        $parts = explode("_", str_replace("-", "_", str_replace($tablePrefix, "", $tableName)));
                        $className = "";
                        foreach($parts as $part){
                            $className .= ucfirst($part);
                        }


                        $tableClassName = "\\app\\canal\\".$canalConf['allows'][$schema]."\\table\\{$className}";

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
                                    echo "Run insert:" . $tableClassName . "\n";
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
                                echo "Table:{$tableName},Run update:" . $tableClassName . "\n";
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
