<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-27
 * Time: 14:13
 */

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\models\Integral;
use app\models\IntegralDeduct;
use app\models\IntegralRecord;
use app\models\User;
use Yii;

class IntegralController extends ApiController
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginFilter::class,
            ],
        ]);
    }

    /**
     * 红包券管理中心页面
     * @Author bing
     * @DateTime 2020-10-13 09:58:24
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionCenter(){
        $reques_data = $this->requestData;
        $type = $reques_data['type'] ?? Integral::TYPE_ALWAYS;
        if(!in_array($type,[Integral::TYPE_ALWAYS,Integral::TYPE_DYNAMIC]))
            return $this->error('type参数错误');
        $controller_type = $reques_data['controller_type'] ?? 0;
        $user_id = Yii::$app->user->id ?? 0;
        //查询用户的红包券、积分券余额
        $wallet = User::getUserWallet($user_id);
        $where = array(
            ['=','controller_type',$controller_type],
            ['=','user_id',$user_id],
            ['=','mall_id',Yii::$app->mall->id ?? 0],
            ['=','type',$type],
        );
        $params = array(
            'select'=>'id,user_id,money,desc,before_money,type,expire_time,status,created_at',
            'where' => $where,
            'limit' => $this->pageSize,
            'order' => 'id DESC'
        );
        $integral_list = IntegralRecord::listPage($params,false,true);
        return $this->success('success',compact('wallet','integral_list'));
    }

    /**
     * 红包券、积分券发放计划
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionPlan(){
        $reques_data = $this->requestData;
        $controller_type = $reques_data['controller_type'] ?? 0;
        $user_id = Yii::$app->user->id ?? 0;
        $where = array(
            ['=','user_id',$user_id],
            ['=','controller_type',$controller_type],
            ['=','mall_id',Yii::$app->mall->id ?? 0]
        );
        $params = array(
            'where' => $where,
            'limit' => $this->pageSize,
            'order' => 'id DESC'
        );

        $plan = Integral::listPage($params,false,true);
        $status_list = Integral::$status_list;
        $type_list = Integral::$type_list;
        $unit_list = Integral::$unit_list;
        return $this->success('success',compact('status_list','type_list','unit_list','plan'));
    }
    
    /**
     * 动态红包券、积分券变动明细
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionDeductList(){
        $reques_data = $this->requestData;
        $controller_type = $reques_data['controller_type'] ?? 0;
        $user_id = Yii::$app->user->id ?? 0;
        $record_id = $reques_data['record_id'] ?? 0;
        if($record_id < 1) return $this->error('record_id参数错误');
        $where = array(
            ['=','controller_type',$controller_type],
            ['=','user_id',$user_id],
            ['=','record_id',$record_id],
            ['=','mall_id',Yii::$app->mall->id ?? 0]
        );
        $params = array(
            'select'=>'money,desc,before_money,created_at',
            'where' => $where,
            'limit' => $this->pageSize,
            'order' => 'id DESC'
        );

        $deduct = IntegralDeduct::listPage($params,false,true);
        return $this->success('success',compact('deduct'));
    }
}