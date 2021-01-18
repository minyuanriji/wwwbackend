<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金接口处理类
 * Author: zal
 * Date: 2020-05-26
 * Time: 10:30
 */

namespace app\plugins\business_card\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\business_card\cache\MessageCenterCache;
use app\plugins\business_card\forms\common\BusinessCardTrackLogCommon;
use app\plugins\business_card\forms\common\Common;
use app\plugins\business_card\models\BusinessCard;
use app\plugins\business_card\models\BusinessCardTag;
use app\plugins\business_card\models\BusinessCardTrackLog;

class MessageCenterForm extends BaseModel
{
    private $key = "jxmall_message_";
    public $form_data;
    public $user_id;
    public $target_user_id = 0;
    private $messageCenterCache;

    public function __construct($config = [])
    {
        $this->messageCenterCache = new MessageCenterCache();
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['user_id','target_user_id'], 'string'],
        ];
    }

    public function addMessage(){
        $key = $this->key."user_".$this->user_id."_".$this->target_user_id;
        $target_key = $this->key."user_".$this->target_user_id."_".$this->user_id;

        if(!\Yii::$app->redis->exists($target_key)){
            \Yii::$app->redis->set($key,json_encode($this->form_data,JSON_UNESCAPED_UNICODE));
        }else{
            \Yii::$app->redis->set($target_key,json_encode($this->form_data,JSON_UNESCAPED_UNICODE));
        }
        //\Yii::$app->redis->DEL('user_list_'.$this->user_id);
        /** @var Redis $redis */
        //\Yii::$app->redis->sadd($this->key.'user_'.$this->user_id,json_encode($this->form_data,JSON_UNESCAPED_UNICODE));
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"添加成功");
    }

    public function getMessage(){
        if(empty($this->target_user_id)){
            $returnData = $this->allList();
        }else{
            $returnData = $this->single();
        }
        return $returnData;
    }

    public function allList(){
        $returnData = [];
        $keys = \Yii::$app->redis->keys($this->key.'user_'.$this->user_id.'*');
        $targetKeys = \Yii::$app->redis->keys($this->key.'user_*_'.$this->user_id);
        $keys = array_merge($keys,$targetKeys);
        //exit;
        //$returnData = \Yii::$app->redis->smembers('user_list_'.$this->user_id);
        if(!empty($keys)){
//            $returnData = json_decode($returnData,true);
            foreach ($keys as $key){
                try{
//                    echo "true:".$key."\n";
                    $value = \Yii::$app->redis->get($key);
                    $data = json_decode($value,true);
                    if(!empty($data)){
                        $returnData[] = $data;
                    }
                }catch (\Exception $ex){
//                    echo "error:".$key."\n";
                    continue;
                }
            }
        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"ok",["list" => $returnData]);
    }

    public function single(){
        $returnData = [];
        $key = $this->key."user_".$this->user_id."_".$this->target_user_id;
        $target_key = $this->key."user_".$this->target_user_id."_".$this->user_id;
        try{
            $result = \Yii::$app->redis->get($key);
            if(!empty($result)){
                $returnData = json_decode($result,true);
            }else{
                $result = \Yii::$app->redis->get($target_key);
                if(!empty($result)){
                    $returnData = json_decode($result,true);
                }
            }
        }catch (\Exception $ex){

        }
        return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"ok",["list" => $returnData]);
    }
}