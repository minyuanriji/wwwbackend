<?php
namespace app\models;

use app\component\jobs\ParentChangeJob;
use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\Mall;
use app\models\User;
use app\models\RelationSetting;
use app\logic\RelationLogic;
use Exception;
use Yii;

class IntegralRecord extends BaseActiveRecord{

    
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%integral_record}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['user_id','money','type'], 'required'],
            [['type'],'in','range'=>[1,2]],
            [['status'],'in','range'=>[1,2,3]],
            [['mall_id','user_id','expire_time','created_at', 'source_id','updated_at'], 'integer'],
            [['money','before_money','controller_type'], 'number'],
            ['source_table','string'],
            [['desc'],'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(){
        return [
            'id'=>'ID',
            'controller_type'=>'卡券类型',
            'mall_id'=>'商城ID',
            'user_id'=>'用户ID',
            'money'=>'资金变动(支持负数)',
            'desc'=>'说明',
            'before_money'=>'变动前的金额',
            'type'=>'积分类型',
            'expire_time'=>'过期时间',
            'source_id'=>	'来源ID',
            'source_table'=> '来源表',
            'status'=>'状态',
            'created_at'=>'创建时间',
            'updated_at'=>'更新时间'
        ];
    }

    public function getUser(){
        return $this->hasOne(User::class,['id'=>'user_id']);
    }

    public function getDeduct(){
        return $this->hasMany(IntegralDeduct::class, ['record_id' => 'id']);
    }   
    /**
     * 新增记录
     * @Author bing
     * @DateTime 2020-09-22 11:21:42
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param array $log
     * @return void
     */
    public static function record(array $log, $parentid=0){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $model = new self();
            $model->loadDefaultValues();
            $model->attributes = $log;
            $res = $model->save();
            if($res === false){
                throw new Exception($model->getErrorMessage());
            }
            $wallet = User::findOne($log['user_id']);

            if($log['controller_type'] == 1){
                $res = UserIntegralForm::record($wallet, $model);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
            }else{
                switch($log['type']){
                    case Integral::TYPE_ALWAYS:
                        $wallet->static_score += $log['money'];
                        break;
                    case Integral::TYPE_DYNAMIC:
                        $wallet->score += $log['money'];
                        break;
                }
                //$wallet->dynamic_score = $wallet->score;
                $wallet->total_score   = $wallet->static_score + $wallet->score;

                //绑定积分券所属上级
                $beforeParentId = $wallet['parent_id'];
                if(!$wallet['parent_id'] && $parentid > 0){
                    $resx = self::checkBindParent($wallet,$parentid);
                    if($resx){
                        $wallet->parent_id        = $parentid;
                        $wallet->second_parent_id = $resx['parent_id'];
                        $wallet->third_parent_id  = $resx['second_parent_id'];
                        $wallet->junior_at        = time();
                    }
                }

                //修改经销商的余额、积分(还有等级)
                $resxx = $wallet->save(false);
                if($resxx === false) {
                    throw new Exception($wallet->getErrorMessage());
                }/*else{
                    if($beforeParentId == 0 && $parentid > 0){ //变更上下级归属
                        $wallet->bindParent($beforeParentId);
                    }
                }*/
            }

            $transaction->commit();
            return true;
        }catch(\Exception $e){
            $transaction->rollBack();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 积分过期处理[定时器处理]
     * @Author bing
     * @DateTime 2020-10-08 09:19:19
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function expireIntegralHandle(){
        $now = time();
        $expire_list = self::find()
        ->where(array('<=','expire_time',$now))
        ->andWhere(array('=','status',1))
        ->andWhere(array('=','type',Integral::TYPE_DYNAMIC))
        ->limit(100)
        ->all();
//        \Yii::$app->redis -> set('key2',json_encode($expire_list));
        if(!empty($expire_list)){
            foreach($expire_list as $expire){
                Yii::$app->mall = Mall::findOne(array('id'=>$expire['mall_id']));
                $wallet = User::getUserWallet($expire['user_id']);
                if(empty($wallet)) continue;
                $dynamic_integral = $wallet['dynamic_integral'];
                $dynamic_score = $wallet['dynamic_score'];
    
                $deduct = array(
                    'mall_id'=> $expire['mall_id'],
                    'user_id'=> $expire['user_id'],
                    'before_money'=> $expire['controller_type']==1?$dynamic_integral:$dynamic_score,
                    'record_id' => $expire['id']
                );
    
                //查询当前金豆券的已经抵扣金额
                $already_deduct = IntegralDeduct::countIntegralDeduct($expire['id']);
                $can_deduct_money = $expire['money'] - $already_deduct;

                if($expire['controller_type'] == 1){
                    if($dynamic_integral > 0 && intval(bcmul($dynamic_integral,100)) >= intval(bcmul($can_deduct_money,100))){
                        $deduct['money'] = -1 *  $can_deduct_money;
                        $deduct['desc'] = '金豆券('.$expire['id'].')超过过期时间：'.date('Y-m-d H:i:s',$expire['expire_time']).',清零余额';
                        //足够扣除,则正常扣减 ：金豆券
                    }else{
                        if($dynamic_integral != 0){
                            $deduct['money'] = -1 *  $dynamic_integral;
                            $deduct['desc'] = '金豆券('.$expire['id'].')超过过期时间：'.date('Y-m-d H:i:s',$expire['expire_time']).',清零余额';
                        }
                    }
                }else{
                    if($dynamic_score > 0 && intval(bcmul($dynamic_score,100)) >= intval(bcmul($can_deduct_money,100))){
                        $deduct['money'] = -1 *  $can_deduct_money;
                        $deduct['desc'] = '积分券('.$expire['id'].')超过过期时间：'.date('Y-m-d H:i:s',$expire['expire_time']).',清零余额';
                        //足够扣除,则正常扣减 ：积分券
                    }else{
                        if($dynamic_score != 0){
                            $deduct['money'] = -1 *  $dynamic_score;
                            $deduct['desc'] = '积分券('.$expire['id'].')超过过期时间：'.date('Y-m-d H:i:s',$expire['expire_time']).',清零余额';
                        }
                    }
                }
                //更新状态
                $expire->status = 2;
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    $res = $expire->save();
                    if($res === false) throw new Exception($expire->getErrorMessage());
                    if($dynamic_integral != 0){
                        //有资金变动就记录
                        $res = IntegralDeduct::deduct($deduct,$expire['controller_type']);
                        if($res === false) throw new Exception(IntegralDeduct::getError());
                    }
                    $transaction->commit();
                }catch(\Exception $e){
                    $transaction->rollBack();
                    self::$error = $e->getMessage();
                    return false;
                }
            }
        }
    }

    /**
     * 获取即将过期的积分、金豆券
     * @Author bing
     * @DateTime 2020-10-08 19:07:16
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return array
     */
    public static function getIntegralAscExpireTime($user_id,$ctype=0){
        return self::find()->where(array(
            'controller_type'=>$ctype,
            'type' => Integral::TYPE_DYNAMIC,
            'status' => 1,
            'user_id' => $user_id
        ))->with(['deduct'])->andWhere("expire_time>'".time()."'")
        ->orderBy('expire_time ASC')->all();
    }

    /**
     * 通过订单获取静态积分的扣减记录
     * @Author bing
     * @DateTime 2020-10-09 15:55:57
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $order
     * @return array
     */
    public static function getDeductRecordByOrder($order,$ctype=0){
        return self::find()
        ->where(array('controller_type'=>$ctype,'type'=>1,'user_id'=>$order['user_id'],'source_table'=>'order','source_id'=>$order['id']))
        ->andWhere(array('<','money',0))
        ->one();
    }

    public static function checkBindParent($user, $parent_id)
    {
        $relation = RelationSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'use_relation' => 1, 'is_delete' => 0]);
        $parent = User::findOne($parent_id);
        if (!$relation) {
            //throw new Exception('未启用关系链'.$parent_id);
            return false;
        }
        elseif ($parent_id == $user->id) {
            //throw new Exception('自己不能绑定自己'.$parent_id);
            return false;
        }
        // elseif ($user->is_inviter == 1) {
        //     //throw new Exception('用户自身是分销商'.$parent_id);
        //     return false;
        // }
        elseif ($user->parent_id != 0) {
            //throw new Exception('用户存在上级'.$parent_id);
            return false;
        }
        elseif (!$parent) {
            //throw new Exception('绑定的上级用户不存在'.$parent_id);
            return false;
        }
        elseif (!$parent->is_inviter) {
            //throw new Exception('绑定的上级用户没有推广资格'.$parent_id);
            return false;
        }
        else{
            return $parent;
        }
        
    }
}
