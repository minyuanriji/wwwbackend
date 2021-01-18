<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-07
 * Time: 16:20
 */

namespace app\controllers\mall;


use app\forms\mall\finance\BalanceLogListForm;
use app\forms\mall\finance\CashForm;
use app\forms\mall\finance\CashListForm;
use app\forms\mall\finance\IncomeLogListForm;
use app\forms\mall\finance\ScoreLogListForm;
use app\forms\mall\finance\SettingForm;
use app\forms\mall\finance\UserFinanceListForm;
use app\models\Integral;
use app\models\IntegralDeduct;
use app\models\IntegralRecord;
use Yii;
use app\controllers\business\WithdrawDeposit;

class FinanceController extends MallController
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-15
     * @Time: 9:58
     * @Note:用户财务列表
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new UserFinanceListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('index');
        }
    }

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-15
     * @Time: 9:58
     * @Note:体现记录
     * @return string|\yii\web\Response
     */
    public function actionCash()
    {

        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
            } else {
                $form = new CashListForm();
                $form->attributes = \Yii::$app->request->get();
                return $this->asJson($form->search());
            }
        }

        return $this->render('cash');
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-30
     * @Time: 10:15
     * @Note:提现处理
     */
    public function actionCashApply()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new CashForm();
                if(\Yii::$app->request->post()['status'] == 2){
                    (new WithdrawDeposit()) -> getCashApply(\Yii::$app->request->post());
                }
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            }
        }

    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-13
     * @Time: 14:31
     * @Note:余额记录
     * @return string|\yii\web\Response
     */

    public function actionBalanceLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new BalanceLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('balance-log');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-13
     * @Time: 14:31
     * @Note:积分记录
     * @return bool|string|\yii\web\Response
     */
    public function actionScoreLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new ScoreLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('score-log');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-14
     * @Time: 17:54
     * @Note:收入记录
     * @return string|\yii\web\Response
     */
    public function actionIncomeLog()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new IncomeLogListForm();
            $form->attributes = \Yii::$app->request->get();
            return $this->asJson($form->getList());
        } else {
            return $this->render('income-log');
        }
    }


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-05-15
     * @Time: 9:54
     * @Note:余额、积分、微信支付等设置
     * @return string|\yii\web\Response
     */
    public function actionSetting()
    {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost) {
                $form = new SettingForm();
                $form->attributes = \Yii::$app->request->post('form');
                return $this->asJson($form->save());
            } else {
                $form = new SettingForm();
                return $this->asJson($form->getSetting());
            }
        } else {
            return $this->render('setting');
        }
    }

    /**
     * 购物券发放计划
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionIntegralPlan(){
        if (\Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $keyword = $request->get('keyword', '');
            $where = array(
                ['=','i.mall_id',Yii::$app->mall->id ?? 0],
                ['=','i.controller_type',1]
            );
            $params = array(
                'alias' => 'i',
                'where' => $where,
                'limit' => $this->pageSize,
                'joinWith' => [
                    'user' => function ($query) use ($keyword) {
                        $query
                        ->select('id,username,nickname,avatar_url')
                        ->orWhere(['or',array('like', 'nickname', $keyword . '%', false), array('like', 'username', $keyword . '%', false)]);
                    }
                ],
                'order' => 'id DESC'
            );

            $plan = Integral::listPage($params,false,true);
            $status_list = Integral::$status_list;
            $type_list = Integral::$type_list;
            $unit_list = Integral::$unit_list;
            return $this->success('success',compact('status_list','type_list','unit_list','plan'));
        }else {
            return $this->render('integral-plan');
        }
    }
    
    /**
     * 购物券管理中心页面
     * @Author bing
     * @DateTime 2020-10-13 09:58:24
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionIntegralList(){
        if (\Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $keyword = $request->get('keyword', '');
            $where = array(
                ['=','i.mall_id',Yii::$app->mall->id ?? 0],
                ['=','i.controller_type',1]
            );
            $where = array(
                ['=','mall_id',Yii::$app->mall->id ?? 0],
                ['=','controller_type',1]
            );
            $params = array(
                'select'=>'id,user_id,money,desc,before_money,type,expire_time,status,created_at',
                'where' => $where,
                'limit' => $this->pageSize,
                'order' => 'id DESC'
            );
            $integral_list = IntegralRecord::listPage($params,false,true);
            return $this->success('success',compact('integral_list'));
        }else{
            return $this->render('integral-list');
        }
    }

    /**
     * 动态购物券变动明细
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionIntegralDeductList(){
        $reques_data = $this->requestData;
        $user_id = Yii::$app->user->id ?? 0;
        $record_id = $reques_data['record_id'] ?? 0;
        if($record_id < 1) return $this->error('record_id参数错误');
        $where = array(
            ['=','user_id',$user_id],
            ['=','record_id',$record_id],
            ['=','mall_id',Yii::$app->mall->id ?? 0],
            ['=','controller_type',1]
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

    /**
     * 积分券发放计划
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionScorePlan(){
        if (\Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $keyword = $request->get('keyword', '');
            $where = array(
                ['=','i.mall_id',Yii::$app->mall->id ?? 0],
                ['=','i.controller_type',0]
            );
            $params = array(
                'alias' => 'i',
                'where' => $where,
                'limit' => $this->pageSize,
                'joinWith' => [
                    'user' => function ($query) use ($keyword) {
                        $query
                        ->select('id,username,nickname,avatar_url')
                        ->orWhere(['or',array('like', 'nickname', $keyword . '%', false), array('like', 'username', $keyword . '%', false)]);
                    }
                ],
                'order' => 'id DESC'
            );

            $plan = Integral::listPage($params,false,true);
            $status_list = Integral::$status_list;
            $type_list = Integral::$type_list;
            $unit_list = Integral::$unit_list;
            return $this->success('success',compact('status_list','type_list','unit_list','plan'));
        }else {
            return $this->render('score-plan');
        }
    }
    
    /**
     * 积分券管理中心页面
     * @Author bing
     * @DateTime 2020-10-13 09:58:24
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionScoreList(){
        if (\Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $keyword = $request->get('keyword', '');
            $where = array(
                ['=','i.mall_id',Yii::$app->mall->id ?? 0],
                ['=','i.controller_type',0]
            );
            $where = array(
                ['=','mall_id',Yii::$app->mall->id ?? 0],
                ['=','controller_type',0]
            );
            $params = array(
                'select'=>'id,user_id,money,desc,before_money,type,expire_time,status,created_at',
                'where' => $where,
                'limit' => $this->pageSize,
                'order' => 'id DESC'
            );
            $integral_list = IntegralRecord::listPage($params,false,true);
            return $this->success('success',compact('integral_list'));
        }else{
            return $this->render('score-list');
        }
    }

    /**
     * 动态购物券变动明细
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public function actionScoreDeductList(){
        $reques_data = $this->requestData;
        $user_id = Yii::$app->user->id ?? 0;
        $record_id = $reques_data['record_id'] ?? 0;
        if($record_id < 1) return $this->error('record_id参数错误');
        $where = array(
            ['=','user_id',$user_id],
            ['=','record_id',$record_id],
            ['=','mall_id',Yii::$app->mall->id ?? 0],
            ['=','controller_type',0]
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