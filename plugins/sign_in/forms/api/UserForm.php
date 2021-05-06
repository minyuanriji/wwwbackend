<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件接口用户表单类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\api;

use app\core\ApiCode;
use app\forms\common\coupon\CouponCommon;
use app\forms\common\coupon\UserCouponCenter;
use app\models\UserCoupon;
use app\plugins\sign_in\forms\common\Common;
use app\plugins\sign_in\models\Coupon;
use app\plugins\sign_in\models\MemberLevel;
use app\plugins\sign_in\models\SignIn;
use app\plugins\sign_in\models\SignInAwardConfig;
use app\plugins\sign_in\models\SignInUser;

class UserForm extends ApiModel
{
    public $is_remind;
    public $user_id;
    public $limit = 10;
    public $page = 1;
    const COUPON_TYPE = 4;

    public function rules()
    {
        return [
            [['is_remind','user_id','page'], 'integer']
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $common = Common::getCommon($this->mall);
            $config = $common->getConfig();
            if (!$config || $config['status'] != 1) {
                throw new \Exception('未开启签到');
            }

            $signInUser = new SignInUser();
            //查看是否签过到了
            $startTime = strtotime(date("Y-m-d",time()));

            $isSignIn = $signInUser->getUserSignInStatus($this->user->id,$startTime,time());
            if (!empty($isSignIn)){
                throw new \Exception('无需重复签到');
            }


            //添加签到日志
            $saveLog = $this->saveUserLog($config->config_at);
            if ($saveLog['status'] != 1){
                throw new \Exception('签到失败');
            }
            //添加奖励
            $saveSigIn = $this->saveSigIn($saveLog['continue']);
            if ($saveSigIn['status'] != true){
                throw new \Exception('签到失败');
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $saveSigIn['remark']
            ];
        } catch (\Exception $exception) {

            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getSignInAward(){
        $mall_id = \Yii::$app->mall->id;

        $query = SignIn::find()->where([
            'mall_id' => $mall_id,
        ]);

        $query->andWhere(['user_id'=>$this->user_id]);

        //排序
        $orderByColumn = "id";
        $orderByType = " desc";
        $orderBy = $orderByColumn." ".$orderByType;

        $query->asArray()->orderBy($orderBy);

        $params["limit"] = $this->limit;
        $params["page"] = $this->page;



        $fields = ['id','number','type','day','remark','created_at'];

        if(!empty($fields)){
            $query->select($fields);
        }
        $pagination = null;
        if(isset($params["limit"]) && isset($params["page"])){
            $query->page($pagination, $params['limit'], $params['page']);
        }

        $list = $query->asArray()->all();


        $mall_members = MemberLevel::findAll(['mall_id' => $mall_id, 'status' => 1, 'is_delete' => 0]);
        if(isset($params["limit"]) && isset($params["page"])) {
            $returnData["list"] = $list;
            $returnData["pagination"] = $pagination;
            $returnData["mall_members"] = $mall_members;
        }else{
            $returnData = $list;
        }

        return $this->returnApiResultData(ApiCode::CODE_SUCCESS, "", $returnData);
    }

    /**
     * 添加奖励
     * @param $continue
     */
    private function saveSigIn($continue){
        //日常奖励
        //查看日常奖励
        $signInAwardConfig = new SignInAwardConfig();
        $dailyReward = $signInAwardConfig->getAwardList(['mall_id'=>$this->mall->id,'status'=>1],['id','number','coupon_id','type','day','status']);
        //查看今天可以领取的连续签到奖励
        $successive = $signInAwardConfig->getAwardList(['mall_id'=>$this->mall->id,'day'=>$continue,'status'=>2],['id','number','coupon_id','type','day','status']);

        //合并数组
        $reardList = array_merge($dailyReward,$successive);

        $integral = [];
        $coupon   = [];
        foreach ($reardList as $item){
            if ($item['type'] == SignInAwardConfig::TYPE_COUPON){
                $coupon[] = $item;
            }else{
                $integral[] = $item;
            }
        }

        //开始加积分
        $integraNum = 0;
        if (!empty($integral)){
            $res = $this->addScore($integral);
            if ($res['status'] == false){
                return ['status'=>false];
            }
            $integraNum = $res['num'];
        }

        //开始发放优惠券
        $couponNum = 0;
        if (!empty($coupon)){
            $res = $this->addCoupon($coupon);
            if ($res['status'] == false){
                return ['status'=>false];
            }
            $couponNum = $res['num'];
        }

        if (!empty($integraNum) && !empty($couponNum)){
            $remark = '恭喜获得'.$integraNum.'积分，'.$couponNum.'张优惠券。';
        }elseif (!empty($couponNum)){
            $remark = '恭喜获得'.$couponNum.'张优惠券。';
        }elseif (!empty($integraNum)){
            $remark = '恭喜获得'.$integraNum.'积分。';
        }else{
            $remark = '';
        }
        return ['status'=>true,'remark'=>$remark];
    }

    //添加优惠券
    private function addCoupon($list){
        //查看优惠券是否过期，是就剔除
        $couponId = array_filter(array_unique(array_column($list,'coupon_id')));

        //以id为键
        $couponModel = new Coupon();
        $common = Common::getCommon($this->mall);
        $couponNameList = $couponModel->getCouponList($couponId);
        $couponNameList = $common->arrayUnderReset($couponNameList,'id');

        //查看是否有失效的券
        $result = [];

        //优惠券数量
        $num = 0;
        foreach ($list as $item) {
            //如果存在就送券
            if (!isset($couponNameList[$item['coupon_id']])){
                unset($item);
                continue;
            }
            //送券
            $item['number'] = (int)$item['number'];
            if ($item['number'] > 1) {
                for ($i = 0; $i < $item['number']; $i++) {

                    $res = $this->couponSave($item['coupon_id']);
                    array_push($result,$res);
                }
            } else {
                $res = $this->couponSave($item['coupon_id']);
                array_push($result,$res);
            }
            //添加日志
            $data = [];
            $data[] = $this->mall->id;
            $data[] = $this->user->id;
            $data[] = $item['number'];
            $data[] = $item['type'];
            $data[] = $item['day'];
            $data[] = $item['status'];
            $data[] = time();
            $data[] = time();
            $data[] = $this->user->access_token;
            $data[] = $item['id'];
            $data[] = $item['status']==1?'普通签到获得'.$item['number'].'张'.$couponNameList[$item['coupon_id']]['name'].'优惠券':'连续签到'.$item['day'].'天获得'.$item['number'].'张'.$couponNameList[$item['coupon_id']]['name'].'优惠券';
            $newData[] = $data;

            $num+=$item['number'];
        }

        //添加奖励日志
        $signInUser = new SignInUser();
        $res = $signInUser->insertArray($newData);
        array_push($result,$res);


        if (empty($result) || $this->is_ok($result)){
            return ['status'=>true,'num'=>$num];
        }
        return ['status'=>false,'num'=>$num];


    }

    public function is_ok(&$rs_row = array())
    {
        $rs = true;

        if (in_array('0', $rs_row) || in_array(false, $rs_row) || in_array('', $rs_row) || empty($rs_row))
        {
            $rs = false;
        }

        return $rs;
    }

    public function couponSave($couponId){

        $common = new CouponCommon(['coupon_id' => $couponId], false);
        $common->user = \Yii::$app->user->identity;

        $coupon = $common->getDetail();
        $class = new UserCouponCenter($coupon, $common->user);
        if ($common->receive($coupon, $class, UserCoupon::$RECEIVE_TYPES[self::COUPON_TYPE])) {
            return true;
        }
        return false;
    }

    //添加积分
    private function addScore($list){
        $newData = [];
        //积分数量
        $num = 0;
        foreach ($list as $k=>$v){
            $v['number'] = (int)$v['number'];
            $data = [];
            $data[] = $this->mall->id;
            $data[] = $this->user->id;
            $data[] = $v['number'];
            $data[] = $v['type'];
            $data[] = $v['day'];
            $data[] = $v['status'];
            $data[] = time();
            $data[] = time();
            $data[] = $this->user->access_token;
            $data[] = $v['id'];
            $data[] = $v['status']==1?'普通签到获得'.$v['number'].'积分':'连续签到'.$v['day'].'天获得'.$v['number'].'积分';
            $newData[] = $data;
            $num+=$v['number'];
        }
        if ($num > 0){
            \Yii::$app->currency->setUser($this->user)->score->add($num, '签到赠送积分');
        }
        //添加奖励日志
        $signInUser = new SignInUser();
        $res = $signInUser->insertArray($newData);
        if ($res){
            return ['status'=>true,'num'=>$num];
        }
        return ['status'=>false];
    }


    //添加用户签到记录
    private function saveUserLog($configTime){
        $signInUser = new SignInUser();

        //查看上次更改配置后的时间与当前时间的时间差
        $todayDifference = strtotime(date("Y-m-d",time()))-$configTime;//今天的时间差

        $signInUser->continue_start = date('Y-m-d H:i:s', time());
        $signInUser->continue = 1;

        if ($todayDifference > 0 ) {//如果是配置修改时间在今天之前
            //获取昨天的时间戳
            $yd = time() - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));  //昨天

            //获取配置时间与昨日的时间差
            $ydDifference = $yd - $configTime;
            if ($ydDifference > 0) {//修改时间是否在昨天零点之前
                //查看昨天是否签到
                $isSignIn = $signInUser->getUserSignInStatus($this->user->id, $yd, strtotime(date("Y-m-d", time())) - 1);
                if (!empty($isSignIn)) {//如果昨天签过到
                    $signInUser->continue = $isSignIn['continue'] + 1;
                    $signInUser->continue_start = $isSignIn['continue_start'];
                }
            } else {
                //查看昨天更改配置后是否签到
                $isSignIn = $signInUser->getUserSignInStatus($this->user->id, $configTime, strtotime(date("Y-m-d", time())) - 1);
                if (!empty($isSignIn)) {//如果昨天更改配置后签过到
                    $signInUser->continue = $isSignIn['continue'] + 1;
                    $signInUser->continue_start = $isSignIn['continue_start'];
                }
            }

        }
        $signInUser->mall_id = $this->mall->id;
        $signInUser->user_id = $this->user->id;
        $signInUser->created_at = time();
        $signInUser->updated_at = time();

        $res = $signInUser->save();
        if (!$res){
            return ['status'=>-1];
        }
        return ['status'=>1,'continue'=>$signInUser->continue];
    }

    public function save1()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $common = Common::getCommon($this->mall);
            $signInUser = $common->getSignInUser($this->user);
            $common->saveSignInUser($signInUser, $this->attributes);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
