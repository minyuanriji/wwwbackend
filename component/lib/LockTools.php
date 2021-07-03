<?php
namespace app\component\lib;

use Yii;

/**
 * Redis锁
 * @Author bing
 * @DateTime 2020-10-06 15:55:47
 * @copyright: Copyright (c) 2020 广东七件事集团
 */
class LockTools{

    //锁定标志，防解错别人的锁
    public $token = null;

    public function __construct(){
        $this->token = uniqid();
    }

    /**
     * 加一把锁
     * @Author bing
     * @DateTime 2020-10-06 15:53:12
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function lock($lock_name){
        $redis = \Yii::$app->redis;
        if(!$redis->getIsActive()){
             $redis->open();
        }
        return $redis->executeCommand('SET', [$lock_name, $this->token, 'EX', 100, 'NX']);
    }

    /**
     * 解锁
     * @Author bing
     * @DateTime 2020-10-06 15:54:24
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $lock_name
     * @return void
     */
    public function unlock($lock_name){
        $redis = \Yii::$app->redis;
        if(!$redis->getIsActive()){
             $redis->open();
        }
        $script = 'if redis.call("get",KEYS[1]) == ARGV[1] then return redis.call("del",KEYS[1]) else return 0 end';
        return $redis->executeCommand('eval', [$script, 1, $lock_name, $this->token]);
    }
}
