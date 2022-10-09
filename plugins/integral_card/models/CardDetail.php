<?php
namespace app\plugins\integral_card\models;

use app\logic\IntegralLogic;
use app\models\BaseActiveRecord;
use app\models\User;
use app\models\user\User as UserModel;
use app\models\mysql\{UserParent,UserChildren};
use Exception;
use Yii;

class CardDetail extends BaseActiveRecord{

    
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_integral_card_detail}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['user_id','integral_setting','qr_url'], 'required'],
            [['mall_id','user_id','expire_time','created_at', 'updated_at'], 'integer'],
            [['status'],'in','range' => [-1,0,1,2]],
            [['is_delete'],'in','range' => [0,1]],
            [['fee'], 'number']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(){
        return [
            'id' => 'ID',
            'mall_id' => '商城ID',
            'user_id' => '用户ID',
            'card_id' => '卡规则ID',
            'picker_id' => '领取人ID',
            'serialize_no' => '序列号',
            'use_code' => '密码',
            'fee' => '手续费',
            'status' => '状态',
            'integral_setting' => '积分设置',
            'qr_url' => '二维码url',
            'expire_time' => '过期时间',
            'updated_at' => '更新时间',
            'created_at' => '创建时间'
        ];
    }

    public function getUser(){
        return $this->hasOne(User::class,['id'=>'user_id'])->select('id,username,nickname');
    }

    public function getPicker(){
        return $this->hasOne(User::class,['id'=>'picker_id'])->select('id,username,nickname');
    }
    
    public function getCard(){
        return $this->hasOne(Card::class,['id'=>'card_id'])->select('id,name');
    }
    /**
     * 积分充值
     * @Author bing
     * @DateTime 2020-10-12 12:33:04
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param string $serialize_no
     * @param string $use_code
     * @param int $user_id
     * @return boolean|object
     */
    public static function recharge($serialize_no,$use_code,$user_id, &$link_url = null){
        try{
            $model = self::find()
            ->where(array(
                'status' => 0,
                'serialize_no' => $serialize_no,
                'use_code' => $use_code,
            ))
            ->andWhere(array('>','expire_time',time()))
            ->one();
            if(empty($model)) throw new Exception('充值卡不存在或已失效');

            $res = IntegralLogic::rechargeIntegral($model->integral_setting,$user_id,0,$model->user_id);
            if($res === false) throw new Exception('添加积分券发放计划失败');

            $model->status = 1;
            $model->picker_id = $user_id;
            $res = $model->save();
            if($res === false) throw new Exception($model->getErrorMessage());
            //修改卡规则标的统计情况
            $card = Card::findOne(array('id'=>$model->card_id));
            $card->use_num += 1;
            $res = $card->save();
            if($res === false) throw new Exception($card->getErrorMessage());
            $level = (new UserModel()) -> getOneUserInfo($user_id);

            if((empty($level['parent_id']) ||  in_array($level['parent_id'], [GLOBAL_PARENT_ID, 9])) && !$level['lock_parent'] && $model->user_id != $user_id){
                (new UserModel()) -> updateUsers(['parent_id' => $model -> user_id], $user_id);
            }

            if($level['level'] < 4){
                (new UserModel()) -> updateUsers(['level' => 4],$user_id);
            }

            /*if($level['role_type'] == 'user'){
                (new UserModel()) -> updateUsers(['role_type' => 'store'],$user_id);
            }*/

            $link_url = $card->link_url ? json_decode($card->link_url, true) : [];

            return $model;
        }catch(Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 
     * @Author bing
     * @DateTime 2020-10-12 12:33:20
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param int $id
     * @param string $option
     * @return boolean
     */
    public static function operator($id,$option){
        try{
            if($id < 1) throw new Exception('card_detail_id参数错误');
            if(!in_array($option,['delete','forbidden'])) throw new Exception('option参数错误');
            $model = self::findOne($id);
            if(empty($model)) throw new Exception('卡数据未发现');
            switch($option){
                case 'delete':
                    $model->is_delete = 1;
                    return $model->save();
                break;
                case 'forbidden':
                    //这是一个开关
                    if($model->is_delete == 1) throw new Exception('卡数据不存在');
                    if($model->status != 0 && $model->status != -1) throw new Exception('不能禁用此状态的卡');
                    $model->status = $model->status == -1 ? 0 : -1;
                    return $model->save();
                break;
            }
        }catch(Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }
    
}
