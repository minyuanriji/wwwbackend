<?php
namespace app\component\lib;

use Yii;
/**
 * 新增加执行sql时断开重连
 * 数据库连接断开异常
 * errorInfo = [''HY000',2006,'错误信息']
 * Class Command
 * @package common\components
 */
class Command extends \yii\db\Command{
    const EVENT_DISCONNECT = 'disconnect';
    public $retry = false;
    /**
     * 处理修改类型sql的断线重连问题
     * @return int
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function execute(){
        try{
            return parent::execute();
        }catch(\Exception $e){
            if($this->handleException($e))
                return parent::execute();
            throw $e;
        }
    }

    /**
     * 处理查询类sql断线重连问题
     * @param string $method
     * @param null $fetchMode
     * @return mixed
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    protected function queryInternal($method, $fetchMode = null){
        try{
            return parent::queryInternal($method, $fetchMode);
        }catch(\Exception $e){
            if($this->handleException($e)){
                try{
                    return parent::queryInternal($method, $fetchMode);
                }catch(\Exception $e){
                    throw $e;
                }
            }
            
        }
    }

    /**
     * 处理执行sql时捕获的异常信息
     * 并且根据异常信息来决定是否需要重新连接数据库
     * @param \Exception $e
     * @return bool true: 需要重新执行sql false: 不需要重新执行sql
     */
    private function handleException(\Exception $e){
        //如果不是yii\db\Exception异常抛出该异常或者不是MySQL server has gone away
        $offset = stripos($e->getMessage(),'MySQL server has gone away');
        if(($e instanceof \yii\db\Exception) == false OR $offset === false)
            //OR $e->errorInfo[0] != 'HY000' OR $e->errorInfo[1] != 2006)
            return false;

        $this->trigger(static::EVENT_DISCONNECT);

        //将pdo设置从null
        $this->pdoStatement = NULL;
        $this->retry = true;
        //$this->db->resetPdo();
        $this->db->close();
        return true;
    }


    /**
     * 利用$this->retry属性，标记当前是否是数据库重连
     * 重写bindPendingParams方法，当当前是数据库重连之后重试的时候
     * 调用bindValues方法重新绑定一次参数.
     */
    protected function bindPendingParams(){
        if ($this->retry) {
            $this->retry = false;
            $this->bindValues($this->params);
        }
        parent::bindPendingParams();
    }
}