<?php

namespace app\controllers\api;

use app\controllers\api\filters\LoginFilter;
use app\forms\api\integral\IntegralLogForm;
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
     * 金豆券管理中心页面
     * @Author bing
     * @DateTime 2020-10-13 09:58:24
     * @return
     * @copyright: Copyright (c) 2020 广东七件事集团
     */
    public function actionCenter()
    {
        $reques_data = $this->requestData;
        $type = $reques_data['type'] ?? Integral::TYPE_ALWAYS;
        if (!in_array($type, [Integral::TYPE_ALWAYS, Integral::TYPE_DYNAMIC])){
            return $this->error('type参数错误');
        }
        $controller_type = $reques_data['controller_type'] ?? 0;
        $user_id = Yii::$app->user->id ?? 0;
        //查询用户的金豆券、积分券余额
        $wallet = User::getUserWallet($user_id);
        $where = array(
            ['=', 'controller_type', $controller_type],
            ['=', 'user_id', $user_id],
            ['=', 'mall_id', Yii::$app->mall->id ?? 0],
            ['=', 'type', $type],
        );
        $params = array(
            'select' => 'id,user_id,money,desc,before_money,type,expire_time,status,created_at',
            'where' => $where,
            'page' => $reques_data['page'],
            'limit' => $this->pageSize,
            'order' => 'id DESC'
        );
        $integral_list = IntegralRecord::listPage($params, false, true);
        if ($integral_list && isset($integral_list['list'])) {
            foreach ($integral_list['list'] as $key => $item) {
                $total_money = (string)($item['before_money'] + $item['money']);
                if (strpos($total_money, '.')) {
                    $integral_list['list'][$key]['total_money'] = (float)substr($total_money, 0, strpos($total_money, '.') + 3);
                } else {
                    $integral_list['list'][$key]['total_money'] = $total_money;
                }
            }
        }
        return $this->success('success', compact('wallet', 'integral_list'));
    }

    /**
     * 金豆券、积分券发放计划
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @return void
     * @copyright: Copyright (c) 2020 广东七件事集团
     */
    public function actionPlan()
    {
        $reques_data = $this->requestData;
        $controller_type = $reques_data['controller_type'] ?? 0;
        $user_id = Yii::$app->user->id ?? 0;
        $where = array(
            ['=', 'user_id', $user_id],
            ['=', 'controller_type', $controller_type],
            ['=', 'mall_id', Yii::$app->mall->id ?? 0]
        );
        $params = array(
            'where' => $where,
            'limit' => $this->pageSize,
            'order' => 'id DESC'
        );

        $plan = Integral::listPage($params, false, true);
        $status_list = Integral::$status_list;
        $type_list = Integral::$type_list;
        $unit_list = Integral::$unit_list;
        return $this->success('success', compact('status_list', 'type_list', 'unit_list', 'plan'));
    }

    /**
     * 动态金豆券、积分券变动明细
     * @Author bing
     * @DateTime 2020-10-13 14:45:49
     * @return void
     * @copyright: Copyright (c) 2020 广东七件事集团
     */
    public function actionDeductList()
    {
        $reques_data = $this->requestData;
        $controller_type = $reques_data['controller_type'] ?? 0;
        $user_id = Yii::$app->user->id ?? 0;
        $record_id = $reques_data['record_id'] ?? 0;
        if ($record_id < 1) return $this->error('record_id参数错误');
        $where = array(
            ['=', 'controller_type', $controller_type],
            ['=', 'user_id', $user_id],
            ['=', 'record_id', $record_id],
            ['=', 'mall_id', Yii::$app->mall->id ?? 0]
        );
        $params = array(
            'select' => 'money,desc,before_money,created_at',
            'where' => $where,
            'limit' => $this->pageSize,
            'order' => 'id DESC'
        );

        $deduct = IntegralDeduct::listPage($params, false, true);
        return $this->success('success', compact('deduct'));
    }

    /**
     * @Note:金豆记录  2021-08-19 最新
     * @return array
     */
    public function actionIntegralList()
    {
        $form = new IntegralLogForm();
        $form->attributes = $this->requestData;
        return $form->getList();
    }
}