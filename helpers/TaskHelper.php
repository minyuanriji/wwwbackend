<?php
namespace app\helpers;

use Exception;
use Yii;

use function GuzzleHttp\json_encode;

/**
 * 任务处理
 * @Author bing
 * @DateTime 2020-09-28 11:27:05
 * @copyright: Copyright (c) 2020 广东七件事集团
 */
class TaskHelper{
    /**
     * 异步任务提交
     * 
     * @Author bing
     * @DateTime 2020-09-28 16:15:51
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param string $task_name 任务名称
     * @param mixed $data   要处理的数据，如后面两个参数有值，则data为method的参数
     * @param string $handler_class 用来处理的类名，带命名空间
     * @param string $method 类处理的方法
     * @return void
     */
    public static function addAsyncTask($task_name,$data,$handler_class='',$method=''){
        try{
            // 建立socket连接到内部推送端口
            $client = stream_socket_client('tcp://'.TASK_IP_ADDR.':9516', $errno, $errmsg,2);
            if(!$client){
                Yii::error('建立socket连接失败：'."$errmsg ($errno)".PHP_EOL);
                return false;
            }
            // 推送的数据，包含task_name字段，表示任务类型
            $send['task_name'] = $task_name;
            $send['data'] = $data;
            $send['handler_class'] = $handler_class;
            $send['method'] = $method;
            fwrite($client, serialize($send)."\n");
            // 读取推送结果
            $res = '';
            while (!feof($client)) {
                $res .= fgets($client, 1024);
            }
            fclose($client);
            $res = trim($res);
            $res_arr = json_decode($res,true);
            if($res_arr['code'] == 0) throw new Exception($res);
            return true;
        }catch(Exception $e){
            echo '建立socket连接失败：'.$e->getMessage().PHP_EOL;
            Yii::error('建立socket连接失败：'.$e->getMessage().PHP_EOL);
            return false;
        }
        return true;
    }
}