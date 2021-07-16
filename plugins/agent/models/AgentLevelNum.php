<?php

namespace app\plugins\agent\models;

use app\models\BaseActiveRecord;
use Exception;
use Yii;
class AgentLevelNum extends BaseActiveRecord
{
    const SCENE_LEVELUP = 1;
    const SCENE_INVITED = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_agent_level_num}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'agent_id', 'level'], 'required'],
            [['mall_id', 'user_id', 'agent_id', 'level','num','use_num','created_at', 'updated_at'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' =>'ID',
            'mall_id' =>'商城ID',
            'user_id' =>'用户ID',
            'agent_id' =>'经销商ID',
            'level' =>'经销商等级',
            'num' =>'数量',
            'use_num' =>'使用数量',
            'created_at' =>'创建时间',
            'updated_at' =>'修改时间'
        ];

    }

    /**
     * 给代理商加名额
     * @Author bing
     * @DateTime 2020-10-28 18:40:15
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $agent
     * @param [type] $scene
     * @param [type] $invaite_level 邀请等级 scene
     * @return void
     */
    public static function increaseNum($agent,$scene,$invaite_level=0){
        $trans = Yii::$app->db->beginTransaction();
        try{
            $level_info = AgentLevel::find()->where(array('level'=>$agent['level']))->asArray()->one();
            $setting = array();
            switch($scene){
                case self::SCENE_LEVELUP :
                    //升级送名额
                    if(isset($level_info['levelup_give_setting'])){
                        $levelup_setting = json_decode($level_info['levelup_give_setting'],true);
                        if(!empty($levelup_setting)) $setting = $levelup_setting;
                    }
                break;
                case self::SCENE_INVITED :
                    //邀请送名额
                    if(isset($level_info['invited_give_setting'])){
                        $invited_settting = json_decode($level_info['invited_give_setting'],true);
                        if(isset($invited_settting[$invaite_level]) && !empty($invited_settting[$invaite_level])){
                            $setting = $invited_settting[$invaite_level];
                        }
                    }
                break;
            }
            //发放名额
            foreach($setting as $level => $num){
                if($num <= 0) continue;
                $res = self::handleNum($agent,$level,$num);
                if($res === false) throw new Exception(self::getError());
            }
            $trans->commit();
            return true;
        }catch(Exception $e){
            $trans->rollBack();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 增减名额数量
     * @Author bing
     * @DateTime 2020-10-28 19:09:11
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $agent
     * @param [type] $level
     * @param [type] $num
     * @param [type] $type increase增 decrease减 cover更改数量
     * @return void
     */
    public static function handleNum($agent,$level,$num,$type='increase'){
        try{
            //获取当前经销商对应等级的数量
            $model = self::findOne(array('agent_id'=>$agent['id'],'level'=>$level,'mall_id'=>Yii::$app->mall->id));
            switch($type){
                case 'cover' :
                case 'increase' :
                    // 加名额
                    if(empty($model)){
                        $model = new self();
                        $model->mall_id = Yii::$app->mall->id;
                        $model->user_id = $agent['user_id'];
                        $model->agent_id = $agent['id'];
                        $model->level = $level;
                    }
                    $model->num =  $type == 'increase' ? $model->num + $num : $num;
                break;
                case 'decrease' :
                    // 减名额
                    if(empty($model)) throw new Exception('名额不足');
                    $diff = $model->num - $num;
                    if($diff <= 0) throw new Exception('名额不足');
                    $model->num -= $num;
                    $model->use_num += $num;
                break;
            }
            $res = $model->save();
            if($res === false) throw new Exception($model->getErrorMessage());
            return true;
        }catch(Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 设置代理等级数量
     * @Author bing
     * @DateTime 2020-10-29 09:30:43
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $agent_id
     * @return void
     */
    public static function setAgentLevelNum($agent_id,$setting){
        $trans = Yii::$app->db->beginTransaction();
        try{
            if(empty($setting) || !is_array($setting)) throw new Exception('setting参数错误');
            $agent = Agent::findOne(array('id' => $agent_id, 'is_delete' => 0));
            //发放名额
            foreach($setting as $level => $num){
                if($num < 0) continue;
                $res = self::handleNum($agent,$level,$num,'cover');
                if($res === false) throw new Exception(self::getError());
            }
            $trans->commit();
            return true;
        }catch(Exception $e){
            $trans->rollBack();
            self::$error = $e->getMessage();
            return false;
        }
    }
}
