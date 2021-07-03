<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 签到插件-处理签到公共类
 * Author: zal
 * Date: 2020-04-20
 * Time: 14:10
 */

namespace app\plugins\sign_in\forms\common;

use app\models\Formid;
use app\models\User;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\sign_in\forms\common\award\BaseAward;
use app\plugins\sign_in\forms\common\award\ContinueAward;
use app\plugins\sign_in\forms\common\award\NormalAward;
use app\plugins\sign_in\forms\common\award\TotalAward;
use app\plugins\sign_in\forms\common\continue_type\MonthState;
use app\plugins\sign_in\forms\common\continue_type\UnlimitedState;
use app\plugins\sign_in\forms\common\continue_type\WeekState;
use app\plugins\sign_in\forms\mall\SignInAwardConfigForm;
use app\plugins\sign_in\jobs\RemindJob;
use app\plugins\sign_in\models\Coupon;
use app\plugins\sign_in\models\SignInAwardConfig;
use app\plugins\sign_in\models\SignInConfig;
use app\plugins\sign_in\models\SignInCustomize;
use app\plugins\sign_in\models\SignInQueue;
use app\plugins\sign_in\models\SignIn;
use app\plugins\sign_in\models\SignInUser;
use app\plugins\sign_in\models\SignInUserRemind;
use yii\db\Exception;

class Common extends BaseModel
{
    public $config;
    public static $instance;

    /**
     * 获取公共类
     * @param $mall
     * @return Common
     * @throws \Exception
     */
    public static function getCommon($mall)
    {
        if (self::$instance) {
            return self::$instance;
        }
        $form = new Common();
        $form->mall = $mall;
        self::$instance = $form;
        return $form;
    }

    /**
     * 获取配置
     * @return SignInConfig|null
     */
    public function getConfig()
    {
        if ($this->config) {
            return $this->config;
        }
        $config = SignInConfig::findOne(['mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$config) {
            $config = new SignInConfig();
            $config->mall_id = $this->mall->id;
        }
        $this->config = $config;
        return $config;
    }

    /**
     * 获取所有的奖励配置
     * @return SignInAwardConfig[]
     */
    public function getAwardConfigAll()
    {
        $awardConfigAll = SignInAwardConfig::findAll([
            'mall_id' => $this->mall->id, 'is_delete' => 0
        ]);
        return $awardConfigAll;
    }

    /**
     * 获取普通签到奖励
     * @return SignInAwardConfig|null
     *
     */
    public function getAwardConfigNormal()
    {
        $awardConfigNormal = SignInAwardConfig::findOne([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => 1
        ]);
        return $awardConfigNormal;
    }

    /**
     * 获取普通签到奖励
     * @return SignInAwardConfig|null
     *
     */
    public function getAwardConfigNormalNew($params)
    {
        $query = SignInAwardConfig::find()->select(['number','day','status','coupon_id','type'])->where([
            'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (isset($params['status'])){
            $query->andWhere(['status'=>$params['status']]);
        }
        if (isset($params['type'])){
            $query->andWhere(['type'=>$params['type']]);
        }

        $awardConfigNormal = $query->asArray()
            ->orderBy([ 'day' => SORT_ASC])->all();

        return $awardConfigNormal;
    }

    /**
     * 获取连续签到奖励
     * @param SignInUser $signInUser
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAwardConfigContinue($signInUser)
    {
        $userId = $signInUser ? $signInUser->user_id : 0;
        $continue = $signInUser ? $signInUser->continue : 0;
        $continueStart = $signInUser ? $signInUser->continue_start : '';
        $query = SignIn::find()->alias('us')->where([
            'us.user_id' => $userId, 'us.status' => 2
        ])->keyword($signInUser, ['<=', 'us.day', $continue])
            ->keyword($signInUser, ['>=', 'us.created_at', $continueStart])
            ->select('us.day, us.id');

        $awardConfigContinue = SignInAwardConfig::find()->alias('a')->where([
            'a.mall_id' => $this->mall->id, 'a.is_delete' => 0, 'a.status' => 2
        ])->leftJoin(['us' => $query], 'us.day = a.day')->select(['a.*', 'us.id check'])
            ->orderBy(['us.day' => SORT_ASC, 'a.day' => SORT_ASC])
            ->limit(3)->asArray()->all();
        return $awardConfigContinue;
    }

    /**
     * 获取累计签到奖励
     * @param $user
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAwardConfigTotal($user)
    {
        $userId = $user ? $user->id : 0;
        $query = SignIn::find()->alias('us')->where(['us.user_id' => $userId])->select('us.day, us.id');

        $awardConfigTotal = SignInAwardConfig::find()->alias('a')->where([
            'a.mall_id' => $this->mall->id, 'a.is_delete' => 0, 'a.status' => 3
        ])->leftJoin(['us' => $query], 'us.day = a.day')->select(['a.*', 'us.id check'])
            ->orderBy(['us.day' => SORT_ASC, 'a.day' => SORT_ASC])
            ->limit(1)->asArray()->all();
        return $awardConfigTotal;
    }

    /**
     * 获取用户签到信息
     * @param $user
     * @return SignInUser|null
     */
    public function getSignInUser($user)
    {
        if (!$user) {
            return null;
        }
        $signInUser = SignInUser::findOne(['user_id' => $user->id, 'mall_id' => $this->mall->id, 'is_delete' => 0]);
        if (!$signInUser) {
//            $signInUser = new SignInUser();
//            $signInUser->mall_id = $this->mall->id;
//            $signInUser->user_id = $user->id;
//            $signInUser->total = 0;
//            $signInUser->continue = 0;
//            $signInUser->is_remind = 0;
//            $signInUser->is_delete = 0;
//            $signInUser->save();
        }
        return $signInUser;
    }

    /**
     * 编辑配置
     * @param SignInConfig|null $config
     * @param $attribute
     * @return SignInConfig|string
     * @throws \Exception
     */
    public function addConfig($config, $attribute)
    {
        $config->config_at = time();
        $config->is_delete = 0;
        $config->attributes = $attribute;

        if (!$config->save()) {
            throw new \Exception($this->responseErrorMsg($config));
        }
        return $config;
    }

    /**
     * 编辑奖励
     * @param array $newList
     * @return SignInAwardConfig|null
     * @throws \Exception
     */
    public function addAwardConfig($newList)
    {
        /* @var SignInAwardConfig[] $awardConfigAll */
        $awardConfigAll = SignInAwardConfig::findAll([
            'mall_id' => $this->mall->id,
            'is_delete' => 0
        ]);
        $awardConfigAll = array_map(function ($v) {
            $v->is_delete = 1;
            return $v;
        }, $awardConfigAll);
        $list = [];
        foreach ($newList as $item) {
            $attribute = new SignInAwardConfigForm();
            $attribute->scenario = $item['type'];
            $attribute->attributes = $item;
            $attribute->search();
            //没太懂为什么重组数组要根据日期和状态（个人觉得还要加类型）
            $awardConfig = null;
            foreach ($awardConfigAll as $key => &$value) {
                if ($value->day == $attribute->day && $value->status == $attribute->status) {
                    $awardConfig = $value;
                    // 删除去除的对象
                    unset($awardConfigAll[$key]);
                    break;
                }
            }
            unset($value);
            if (!$awardConfig) {
                $awardConfig = new SignInAwardConfig();
                $awardConfig->day = $attribute->day;
                $awardConfig->status = $attribute->status;
                $awardConfig->mall_id = $this->mall->id;
            }

            $awardConfig->is_delete = 0;
            $awardConfig->number = $attribute->number;
            $awardConfig->type = $attribute->type;
            if ($attribute->type == SignInAwardConfig::TYPE_COUPON){
                $awardConfig->coupon_id = $attribute->coupon_id;
            }
            $list[] = $awardConfig;
        }
        // 重排列数组
        $awardConfigAll = array_values($awardConfigAll);
        $list = array_merge($list, $awardConfigAll);
        /* @var SignInAwardConfig[] $list */
        foreach ($list as $item) {
            if (!$item->save()) {
                throw new \Exception($this->responseErrorMsg($item));
            }
        }
        return $list;
    }

    /**
     * 添加队列处理日志
     * @param $token
     * @param $data
     * @return bool
     */
    public function addQueueData($token, $data)
    {
        $form = new SignInQueue();
        $form->token = $token;
        $form->data = $data;
        $form->save();
        return true;
    }

    /**
     * 通过token获取队列处理结果
     * @param $token
     * @return SignInQueue|null
     */
    public function getQueueData($token)
    {
        $queueData = SignInQueue::findOne(['token' => $token]);
        return $queueData;
    }

    /**
     * 获取今天签到的奖励信息
     * @param $user
     * @return array|\yii\db\ActiveRecord|null|SignIn
     */
    public function getSignInByToday($user)
    {
        if (!$user) {
            return null;
        }
        $start = date('Y-m-d', time());
        $end = date('Y-m-d H:i:s', (strtotime($start) + 86400 - 1));
        $signIn = SignIn::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $user->id, 'status' => 1])
            ->andWhere(['>=', 'created_at', $start])->andWhere(['<=', 'created_at', $end])
            ->orderBy(['id' => SORT_DESC])->one();
        return $signIn;
    }


    /**
     * 获取昨天签到的奖励信息
     * @param $user
     * @return array|\yii\db\ActiveRecord|null|SignIn
     */
    public function getSignInByYesterday($user)
    {
        // 今天凌晨
        $start = date('Y-m-d', time());
        // 昨天凌晨
        $start = date('Y-m-d', strtotime($start) - 86400);
        // 昨天半夜
        $end = date('Y-m-d H:i:s', (strtotime($start) + 86400 - 1));
        $signIn = SignIn::find()
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0, 'user_id' => $user->id, 'status' => 1])
            ->andWhere(['>=', 'created_at', $start])->andWhere(['<=', 'created_at', $end])
            ->orderBy(['id' => SORT_DESC])->one();
        return $signIn;
    }

    /**
     * 获取指定天数指定用户的奖励信息
     * @param integer $day
     * @param User $user
     * @param integer $status
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getSignInByDay($status, $day, $user)
    {
        $sign = SignIn::find()->where([
            'mall_id' => $this->mall->id, 'user_id' => $user->id, 'status' => $status, 'day' => $day, 'is_delete' => 0
        ])->one();
        return $sign;
    }

    /**
     * 获取指定天数的奖励方案
     * @param $status
     * @param $day
     * @return SignInAwardConfig|null
     */
    public function getAwardByDay($status, $day)
    {
        $award = SignInAwardConfig::findOne([
            'mall_id' => $this->mall->id, 'is_delete' => 0, 'status' => $status, 'day' => $day
        ]);
        return $award;
    }

    /**
     * 获取指定token的奖励信息
     * @param $token
     * @param $user
     * @return SignIn|null
     */
    public function getSignInByToken($token, $user)
    {
        $sign = SignIn::findOne(['token' => $token, 'mall_id' => $this->mall->id, 'user_id' => $user->id]);
        return $sign;
    }

    /**
     * 获取指定月用户签到信息
     * @param $month
     * @param $user
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSignInDayByMonth($month,$endTime, $user)
    {
        $sign = SignIn::find()
            ->where(['mall_id' => $this->mall->id, 'user_id' => $user->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['between','created_at',$month,$endTime])->all();
        return $sign;
    }

    /**
     * 获取指定月用户签到信息
     * @param $month
     * @param $user
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSignInDayByMonthNew($month,$endTime, $user)
    {
        $sign = SignInUser::find()
            ->where(['mall_id' => $this->mall->id, 'user_id' => $user->id, 'is_delete' => 0])
            ->andWhere(['between','created_at',$month,$endTime])->asArray()->all();
        return $sign;
    }

    /**
     * 获取所有开启签到提醒的用户
     * @return SignInUser[]
     *
     */
    public function getSignInUserByRemind()
    {
        $signInUserId = SignInUserRemind::find()->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->andWhere(['like', 'date', date('Y-m-d')])
            ->select('user_id');
        $signInUser = SignInUser::find()->alias('cu')->with('user')
            ->where(['cu.is_delete' => 0, 'cu.mall_id' => $this->mall->id, 'cu.is_remind' => 1])
            ->andWhere(['not in', 'cu.user_id', $signInUserId])
            ->leftJoin(['f' => Formid::tableName()], 'f.user_id = cu.user_id')
            ->andWhere(['>', 'f.remains', 0])
            ->andWhere(['!=', 'f.form_id', 'null'])->groupBy('cu.user_id')->all();

        return $signInUser;
    }

    /**
     * 获取最近一次签到提醒时间
     * @param $configTime
     * @return false|int
     */
    public function getRemind($configTime = null)
    {
        if (!$configTime) {
            $configTime = $this->config->time;
        }
        $time = time();
        $delay = strtotime(date('Y-m-d', $time) . $configTime);
        if ($delay < $time) {
            $delay = strtotime(date('Y-m-d', $time + 86400) . $configTime);
        }
        if (!$delay) {
            $delay = time() + 86400;
        }
        return $delay - $time;
    }

    /**
     * 添加已提醒的用户
     * @param $attributes
     * @return bool
     * @throws \Exception
     */
    public function addSignInUserRemind($attributes)
    {
        $form = new SignInUserRemind();
        $form->attributes = $attributes;
        if (!$form->save()) {
            throw new \Exception($this->responseErrorMsg($form));
        }
        return true;
    }

    /**
     * 添加签到提醒定时任务
     * @param int $time
     */
    public function addRemindJob($time = -1)
    {
        if ($time < 0) {
            $time = $this->getRemind();
        }
        \Yii::$app->queue->delay($time)->push(new RemindJob([
            'mall' => $this->mall
        ]));
    }

    /**
     * 获取指定用户指定月份已签到的日期
     * @param $month
     * @param $user
     * @return array
     */
    public function getDay($month, $user)
    {
        if (!$user) {
            return [];
        }

        $endTime = strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m', $month) . '-' . date('t', $month) . ' 23:59:59')));

        /* @var SignIn[] $signInAll */
        $signInAll = $this->getSignInDayByMonth($month,$endTime, $user);
        $signInDay = [];
        foreach ($signInAll as $item) {
            $signInDay[] = date('Y-m-d', $item->created_at);
        }


        return $signInDay;
    }

    /**
     * 获取模板消息发送方法
     * @param $user
     * @return CommonTemplate
     */
    public function getCommonTemplate($user)
    {
        $template = new CommonTemplate();
        $template->user = $user;
        $template->page = 'plugins/sign_in/index/index';
        return $template;
    }

    /**
     * @param SignInUser $signInUser
     * @param $attributes
     * @throws \Exception
     * @return bool
     */
    public function saveSignInUser($signInUser, $attributes)
    {
        $signInUser->attributes = $attributes;
        if (!$signInUser->save()) {
            throw new \Exception($this->responseErrorMsg($signInUser));
        }
        return true;
    }

    /**
     * @param bool $all 是否显示全部
     * @param int $limit
     * @param int $page
     * @return array
     * 获取签到插件的所有用户
     */
    public function getSignInUserAll($limit = 20, $page = 1)
    {
        $signInUser = SignInUser::find()->with('user')
            ->where(['mall_id' => $this->mall->id, 'is_delete' => 0])
            ->page($pagination, $limit, $page)->all();
        return [
            'list' => $signInUser,
            'pagination' => $pagination
        ];
    }

    /**
     * @param int $page
     * @param int $count
     * @return int
     * 已1000条数据为周期进行清除用户连续签到数
     */
    public function clearContinue($page = 1, $count = 0)
    {
        $res = $this->getSignInUserAll(1000, $page);
        /* @var SignInUser[] $signInUserList */
        $signInUserList = $res['list'];
        /* @var Pagination $pagination */
        $pagination = $res['pagination'];
        foreach ($signInUserList as $signInUser) {
            $signInUser->continue = 0;
            $signInUser->continue_start = '';
            if ($signInUser->save()) {
                $count++;
            } else {
                \Yii::error($signInUser->errors);
            }
        }
        if ($pagination->page_count > $page) {
            $page++;
            $this->clearContinue($page, $count);
        }
        return $count;
    }

    /**
     * @param $continueType
     * @return MonthState|UnlimitedState|WeekState|null
     * @throws \Exception
     * 根据清除连续签到的状态获取不同的处理类
     */
    public function getContinueTypeClass($continueType)
    {
        $state = null;
        switch ($continueType) {
            case 1:
                $state = new UnlimitedState();
                break;
            case 2:
                $state = new WeekState();
                break;
            case 3:
                $state = new MonthState();
                break;
            default:
                throw new \Exception('错误的清除连续签到状态码');
        }
        $state->common = $this;
        return $state;
    }

    public function getCustomize()
    {
        $model = SignInCustomize::findOne([
            'mall_id' => $this->mall->id,
            'name' => 'page',
        ]);
        if (!$model) {
            return $model;
        }
        return \Yii::$app->serializer->decode($model->value);
    }

    public function setCustomize($value)
    {
        $model = SignInCustomize::findOne([
            'name' => 'page',
            'mall_id' => $this->mall->id,
        ]);
        if (!$model) {
            $model = new SignInCustomize();
            $model->name = 'page';
            $model->mall_id = $this->mall->id;
        }
        $model->value = \Yii::$app->serializer->encode($value);
        return $model->save();
    }

    /**
     * @param $status
     * @param $day
     * @param SignInUser $signInUser
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getSignInByContinue($status, $day, $signInUser)
    {
        $sign = SignIn::find()->where([
            'mall_id' => $this->mall->id, 'user_id' => $signInUser->user_id, 'status' => $status, 'day' => $day,
            'is_delete' => 0
        ])->andWhere(['<=', 'day', $signInUser->continue])
            ->keyword($signInUser->continue_start, ['>=', 'created_at', $signInUser->continue_start])->one();
        return $sign;
    }

    /**
     * @param $status
     * @return BaseAward
     * @throws \Exception
     */
    public function getAward($status)
    {
        switch ($status) {
            case 1:
                $award = new NormalAward();
                break;
            case 2:
                $award = new ContinueAward();
                break;
            case 3:
                $award = new TotalAward();
                break;
            default:
                throw new \Exception('错误的参数');
        }
        $award->common = $this;
        return $award;
    }

    /**
     * 返回以原数组某个值为下标的新数据
     *
     * @param array $array
     * @param string $key
     * @param int $type 1一维数组2二维数组
     * @return array
     */
    function arrayUnderReset($array, $key, $type = 1)
    {
        if (is_array($array)) {
            $tmp = array();
            foreach ($array as $v) {
                if ($type === 1) {
                    $tmp[$v[$key]] = $v;
                }
                elseif ($type === 2) {
                    $tmp[$v[$key]][] = $v;
                }
            }
            return $tmp;
        }
        else {
            return $array;
        }
    }


    /**
     * 获取用户签到列表
     * @param $user
     * @param string $month
     * @return array
     */
    public function getUserSignInList($user,$firstDay=''){
        //获取日历
        $monthList = [];
        $i = 0;
        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));
        while (date('Y-m-d', strtotime("$firstDay +$i days")) <= $lastDay) {
            $data = [];
            $data['date'] = date('Y-m-d', strtotime("$firstDay +$i days"));
            $data['day']  = (int)date('d', strtotime("$firstDay +$i days"));
            $monthList[] = $data;
            $i++;
        }

        //如果没有登录
        if (!$user){
            foreach ($monthList as $k=>$v){
                $monthList[$k]['is_sign'] = false;
                $monthList[$k]['time'] = '';
            }
            return $monthList;
        }

        $begTime = strtotime($firstDay);
        $endTime = strtotime(date('Y-m-d H:i:s', strtotime(date('Y-m', $begTime) . '-' . date('t', $begTime) . ' 23:59:59')));

        /* @var SignIn[] $signInAll */
        $signInAll = $this->getSignInDayByMonthNew($begTime,$endTime, $user);
        $signInDay = [];
        foreach ($signInAll as $v) {
            $data = [];
            $data['day']  = date('Y-m-d', $v['created_at']);
            $data['time'] = $v['created_at'];
            $signInDay[] = $data;
        }

        //循环查看是否签到
        foreach ($monthList as $k=>$v){
            $monthList[$k]['time'] = '';
            $monthList[$k]['is_sign'] = false;
            foreach ($signInDay as $v1){
                if ($v1['day'] == $v['date']){
                    $monthList[$k]['is_sign'] = true;
                    $monthList[$k]['time'] = $v1['time'];
                    break;
                }
            }
        }
        return $monthList;

    }

    public function getUserList($list,$mallId){
        $begTime = strtotime(date('Y-m-d', time()));
        $endTime = strtotime(date('Y-m-d H:i:s', ($begTime+86399)));
        $userId = array_filter(array_unique(array_column($list,'id')));
        $signInUser = new SignInUser();
        //根据用户id查询签到信息 今天有没有签到
        $signInStatus = $signInUser::find()
            ->where(["is_delete" => self::NO])
            ->andWhere(['mall_id'=>$mallId])
            ->andWhere(['between','created_at',$begTime,$endTime])
            ->andWhere(['in','user_id',$userId])
            ->select('id,user_id,continue,created_at')
            ->asArray()->all();

        //累计优惠券奖励
        $signIn = new SignIn();
        $signInCoupon = $signIn::find()
            ->where(["is_delete" => self::NO,'mall_id'=>$mallId,'type'=>3])
            ->andWhere(['in','user_id',$userId])
            ->select('user_id,sum(number) as number')
            ->groupBy('user_id')
            ->asArray()
            ->all();

        $signInIntegral = $signIn::find()
            ->where(["is_delete" => self::NO,'mall_id'=>$mallId,'type'=>1])
            ->andWhere(['in','user_id',$userId])
            ->select('user_id,sum(number) as number')
            ->groupBy('user_id')
            ->asArray()
            ->all();

        //最新签到时间
        $signInTime = $signInUser::find()
            ->where(["is_delete" => self::NO,'mall_id'=>$mallId])
            ->andWhere(['in','user_id',$userId])
            ->select('user_id,max(created_at) as created')
            ->groupBy('user_id')
            ->asArray()
            ->all();

        //以id为键
        $signInCoupon = $this->arrayUnderReset($signInCoupon,'user_id');
        $signInIntegral = $this->arrayUnderReset($signInIntegral,'user_id');
        $signInStatus = $this->arrayUnderReset($signInStatus,'user_id');
        $signInTime = $this->arrayUnderReset($signInTime,'user_id');

        foreach ($list as $k=>$v){

            $list[$k]['coupon_num'] = 0;
            if (isset($signInCoupon[$v['id']])){
                $list[$k]['coupon_num'] = $signInCoupon[$v['id']]['number'];
            }
            $list[$k]['integral_num'] = 0;
            if (isset($signInIntegral[$v['id']])){
                $list[$k]['integral_num'] = $signInIntegral[$v['id']]['number'];
            }

            $list[$k]['sign_in'] = '未签到';
            if (isset($signInStatus[$v['id']])){
                $list[$k]['sign_in'] = '已签到';
            }
            $list[$k]['continue'] = '暂无';
            if (isset($signInStatus[$v['id']])){
                $list[$k]['continue'] = $signInStatus[$v['id']]['continue'].'天';
            }
            $list[$k]['created'] = '-----';
            if (isset($signInTime[$v['id']])){
                $list[$k]['created'] = date('Y-m-d H:i:s',$signInTime[$v['id']]['created']);
            }
        }

        return $list;

    }


}
