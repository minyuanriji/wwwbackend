<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础model
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:12
 */

namespace app\models;

use Exception;
use Yii;

class Task extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%task}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_name'], 'required'],
            [['retry_times','status', 'created_at', 'updated_at'], 'integer'],
            [['handler_class','method','data','error_msg'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_name' => '任务名称',
            'handler_class' => '执行类',
            'method' => '执行方法',
            'retry_times' => '重试次数',
            'data' => '参数',
            'error_msg' => '错误信息',
            'status' => 	'状态',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * 添加一个失败任务
     * @Author bing
     * @DateTime 2020-10-06 15:13:20
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public  function addFailedTask($task){
        $this->task_name = $task['task_name'];
        $this->handler_class = $task['handler_class'];
        $this->method = $task['method'];
        $this->data = $task['data'];
        $this->error_msg = $task['error_msg'];
        $this->created_at = time();
        return $this->save();
    }
   
    /**
     * 任务失败重试
     * @Author bing
     * @DateTime 2020-10-13 10:09:22
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function reTryFaildTask(){
        $fail_task_list =  self::find()
        ->andWhere(array('=','status',0))
        ->orderBy('created_at ASC')
        ->limit(100)
        ->all();
        //失败任务重新执行
        if(!empty($fail_task_list)){
            foreach($fail_task_list as $fail_task){
                try {
                    $class = $fail_task['handler_class'];
                    $method = $fail_task['method'];
                    $obj = new $class();
                    $res = $obj->$method(unserialize($fail_task['data']));
                    $fail_task->status = 1;
                    $res = $fail_task->delete();
                    echo '执行任务['.$fail_task['task_name'].']成功,状态：'.$res.PHP_EOL;
                } catch (Exception $e) {
                    echo $e->getMessage().PHP_EOL;
                    //处理过程报错
                    $fail_task->retry_times += 1;
                    if($fail_task->retry_times >= 3)  $fail_task->status = 2;
                    $fail_task->error_msg =  $e->getMessage();
                    $res = $fail_task->save();
                    throw $e;
                }
            }
        }
    }
}
