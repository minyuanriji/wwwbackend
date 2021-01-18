<?php
namespace app\plugins\integral_card\models;

use app\logic\AppConfigLogic;
use app\models\BaseActiveRecord;
use app\models\Mall;
use app\models\User;
use Exception;
use Yii;

class Card extends BaseActiveRecord{

    const MAX_GENERATE_NUM = 1000;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_integral_card}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['user_id','integral_setting','generate_num','name'], 'required'],
            [['mall_id','user_id','generate_num','use_num','generate_time','expire_time','created_at', 'updated_at'], 'integer'],
            [['fee'], 'number'],
            [['status'],'in','range' => [0,1]]
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
            'name' => '卡券名称',
            'integral_setting' => '积分设置',
            'generate_num' => '生成数量',
            'use_num' => '使用数量',
            'expire_time' => '过期时间',
            'fee' => '收取手续费',
            'status' => '状态',
            'generate_time' => '生成时间',
            'updated_at' => '更新时间',
            'created_at' => '创建时间'
        ];
    }

    public function getUser(){
        return $this->hasOne(User::class,['id'=>'user_id']);
    }

    public function getCard_detail(){
        return $this->hasMany(CardDetail::class,['id'=>'card_id']);
    }

    /**
     * 添加卡券生成方案
     * @Author bing
     * @DateTime 2020-10-10 14:45:15
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $post
     * @return void
     */
    public static function setData($post){
        try {
            if (isset($post['id']) && $post['id'] > 0) {
                $model = self::findOne(array('mall_id' => Yii::$app->mall->id, 'id' => $post['id']));
                if(empty($model)) throw new Exception('编辑的数据不存在');
                if($model->status != 0) throw new Exception('此卡券已生成数据不能编辑');
            }
            if(empty($model)) $model = new self();
            $user = User::findOne(array('id'=>$post['user_id']));
            if(empty($user)) throw new Exception('未找到绑定用户信息');
            if(self::MAX_GENERATE_NUM < $post['generate_num']) throw new Exception('一次最多生成'.self::MAX_GENERATE_NUM.'张卡');
            $model->mall_id = Yii::$app->mall->id;
            $model->user_id = $post['user_id'];
            $model->name = $post['name'];
            $model->integral_setting = json_encode($post['integral_setting']);
            $model->generate_num = $post['generate_num'];
            $model->expire_time = strtotime($post['expire_time']);
            $model->fee = $post['fee'] ?? 0;
            $model->status = $post['status'] ?? 0;
            $res = $model->save();
            if ($res === false) throw new Exception($model->getErrorMessage());
            return $model->attributes['id'];
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
   
    /**
     * 获取模板详情
     * @Author bing
     * @DateTime 2020-10-10 16:09:32
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $id
     * @return array
     */
    public static function getData($id){
        $detail =  self::find()
        ->where(array('id'=>$id))
        ->with(['user'])
        ->asArray()
        ->one();
        if(empty($detail)) return null;
        $detail['integral_setting'] = json_decode($detail['integral_setting'],true);
        return $detail;
    }

    /**
     * 生成卡ID
     * @Author bing
     * @DateTime 2020-10-10 17:13:47
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function generateCard($card_id){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $card_info = self::find()->where(array('id'=>$card_id))->one();
            if(empty($card_info)) throw new Exception('未找到该数据');
            if($card_info->status != 0) throw new Exception('此卡券模板已生成数据,不能重复生成');
            $deduct_fee = $card_info['fee'] * $card_info['generate_num'];
            
            $table = CardDetail::getTableSchema()->name;
            $columns = array('mall_id','user_id','card_id','picker_id','serialize_no','use_code','fee','status','integral_setting','qr_url','expire_time','created_at','updated_at');
            $insert = array();

            $mallSettings = AppConfigLogic::getMallSettingConfig(["web_url"]);
            $host = isset($mallSettings["web_url"]) ? urldecode($mallSettings["web_url"]) : "";
            $path = "/h5/#/pages/user/integral/rechargeCard?mall_id=".$card_info['mall_id']."&source=".User::SOURCE_SHARE_POSTER;
            //$path = "/h5/#/pages/user/integral/rechargeCard?mall_id=".$card_info['mall_id']."&pid=".$card_info['user_id']."&source=".User::SOURCE_SHARE_POSTER;
            $url = $host . $path;
            // var_dump($create_index);die;
            $id = CardDetail::find()->max('id') ?? 0;
            $now = time();

            for($i = 1; $i <= $card_info['generate_num']; $i++){
                $serialize_no = self::_getSerializeNo($id+$i,6,);
                // echo $serialize_no.PHP_EOL;
                $use_code = rand(100000, 999999);//self::_getUseCode($serialize_no);
                // echo $use_code.PHP_EOL;
                $insert[] = array(
                    'mall_id'=>$card_info['mall_id'],
                    'user_id'=>$card_info['user_id'],
                    'card_id'=>$card_info['id'],
                    'picker_id'=>0,
                    'serialize_no'=>$serialize_no,
                    'use_code'=>$use_code,
                    'fee'=>$card_info['fee'],
                    'status'=>0,
                    'integral_setting'=>$card_info['integral_setting'],
                    'qr_url' => $url,
                    'expire_time' => $card_info['expire_time'],
                    'created_at'=> $now,
                    'updated_at'=> $now
                );
            }
            //批量插入减少数据库IO次数
            $res = Yii::$app->db->createCommand()->batchInsert(
                $table,$columns,
                $insert
            )->execute();
            if($res === false) throw new Exception('生成数据失败');
            //更新状态
            $card_info->status = 1;
            $card_info->generate_time = $now;
            $res = $card_info->save();
            if($res === false) throw new Exception($card_info->getErrorMessage());
            $transaction->commit();
            return true;
        }catch(Exception $e){
            $transaction->rollBack();
            self::$error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * 获取序列号
     * @Author bing
     * @DateTime 2020-10-10 18:08:32
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $id
     * @param [type] $num_length
     * @param [type] $prefix
     * @return string
     */
    private static function _getSerializeNo($id, $num_length, $prefix=null){
        //默认前缀字母
        if(is_null($prefix)){
            $prefix = self::_getRandomString(2,'ABCDEFGHJKLMNPQRSTUVWXYZ');
        }
        //$id超出预设长度 等于 当前id长度
        if(strlen( $id ) > $num_length){
            $num_length = strlen( $id );
        }
        // 基数
        $base = pow(10, $num_length);
        // 生成数字部分
        $mod = $id % $base;
        $digital = str_pad($mod, $num_length, 0, STR_PAD_LEFT);
        $code = sprintf('%s%s', $prefix, $digital);
        return $code;
    }

    /**
     * 生成密码
     * @Author bing
     * @DateTime 2020-10-10 18:36:43
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $serialize_no
     * @return void
     */
    private static function _getUseCode($serialize_no){
        return substr(md5($serialize_no.time()),-6);
    }
    
    private static function _getRandomString($len, $chars=null)
    {
        if (is_null($chars)){
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        } 
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)]; 
        }
        return $str;
    }
}
