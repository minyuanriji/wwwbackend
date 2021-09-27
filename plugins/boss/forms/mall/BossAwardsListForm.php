<?php

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\forms\common\UserIncomeForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardEachLog;
use app\plugins\boss\models\BossAwardMember;
use app\plugins\boss\models\BossAwardRechargeLog;
use app\plugins\boss\models\BossAwards;
use app\plugins\boss\models\BossLevel;

class BossAwardsListForm extends BaseModel
{
    public $keyword;
    public $page;
    public $period_type = [
        'day'   => '天',
        'week'  => '周',
        'month' => '月',
        'year'  => '年',
    ];
    public $period_type_change = [
        'day',
        'week',
        'month',
        'year',
    ];
    public $recharge = [1,148];

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['page', ], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    //查看
    public function search($where = [],$select = '*', $order = 'id desc')
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = BossAwards::find()->select($select);
        if ($where) {
            $query->andWhere($where);
        }
        $list = $query->andWhere([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
        ])->keyword($this->keyword, ['or',['like', 'name', $this->keyword],['like', 'award_sn', $this->keyword]])
            ->page($pagination, 20, $this->page)->orderBy($order)->all();

        $new_value = [];
        $new_list = [];
        if ($list) {
            foreach ($list as $value) {
                $new_value['id'] = $value->id;
                $new_value['award_sn'] = $value->award_sn;
                $new_value['mall_id'] = $value->mall_id;
                $new_value['name'] = $value->name;
                $new_value['status'] = $value->status;
                $new_value['period'] = $value->period;
                $new_value['period_unit'] = $this->period_type[$value->period_unit];
                $new_value['money'] = $value->money;
                $new_value['rate'] = $value->rate;
                $new_value['level_id'] = ($value->level_id && is_string($value->level_id)) ? json_decode($value->level_id,true) : [];
                if ($new_value['level_id']) {
                    $boss_data = BossLevel::find()
                        ->select('id,name')
                        ->andWhere(['and', ['in','id',$new_value['level_id']], ['is_delete' => 0]])
                        ->asArray()
                        ->all();
                    if ($boss_data) {
                        $new_value['level_name'] = $boss_data;
                    } else {
                        $new_value['level_name'] = [];
                    }
                } else {
                    $new_value['level_name'] = [];
                }
                $new_value['created_at'] = date('Y-m-d H:i:s', $value->created_at);
                unset($new_value['level_id']);
                $new_list[] = $new_value;
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $new_list,
                'pagination' => $pagination
            ]
        ];
    }

    //修改
    public function save($post_data)
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if (isset($post_data['id']) && $post_data['id']) {
                $level = BossAwards::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $post_data['id']]);
                if (!$level)
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '奖池不存在'
                    ];

                if ($level->status == 1)
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '请先关闭奖池在修改'
                    ];

            } else {
                $new_max_id = sprintf("%04d", 1);
                $max_id = BossAwards::find()->select('id')->orderBy(['id' => SORT_DESC])->one();
                if ($max_id) {
                    if ($max_id->id > 9999) {
                        $new_max_id = $max_id->id + 1;
                    } else {
                        $new_max_id = sprintf("%04d", $max_id->id+ 1);
                    }
                }
                $top_sn = substr(date('Y', time()), -2) . date('md', time()) . $new_max_id;
                $level = new BossAwards();
                $level->mall_id = \Yii::$app->mall->id;
                $level->award_sn = 'BSH' . $top_sn;
            }
            if (isset($post_data['level_ids'])) {
                $level->level_id = json_encode($post_data['level_ids']);
            }
            $level->name = $post_data['name'];
            $level->status = $post_data['status'];
            $level->period = $post_data['period'];
            $level->period_unit = $post_data['period_unit'];
            $level->rate = $post_data['rate'];
            $level->automatic_audit = $post_data['automatic_audit'];
            if (!$level->save()) {
                throw new \Exception($this->responseErrorMsg($level));
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    //查看详情
    public function getDetail($id)
    {
        $level = BossAwards::findOne(['id' => $id, 'is_delete' => 0]);
        if ($level) {
            $level->period_unit = array_search($level->period_unit,$this->period_type_change);
            $level->level_id = json_decode($level->level_id, true);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $level
            ];
        }
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '',
            'data' => []
        ];
    }

    //删除
    public function del ($id)
    {
        if (!$id) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请传入id'
            ];
        }
        $level = BossAwards::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $id]);
        if ($level) {
            if ($level->status == 1) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '奖池正在进行中，请关闭后删除！'
                ];
            }
            $level->is_delete = 1;
            if (!$level->save()) {
                throw new \Exception($this->responseErrorMsg($level));
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }
    }

    //充值
    public function recharge ($params)
    {
        if (!in_array(\Yii::$app->admin->id,$this->recharge)) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请联系财务或主账号充值！'
            ];
        }
        if (!isset($params['id']) || !$params['id']) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请传入id'
            ];
        }
        if (!isset($params['money']) || !$params['money']) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请填写充值金额'
            ];
        }
        $awards = BossAwards::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $params['id']]);
        if ($awards) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $recharge_model = new BossAwardRechargeLog();
                $recharge_model->mall_id = \Yii::$app->mall->id;
                $recharge_model->award_id = $params['id'];
                $recharge_model->money = $params['money'];
                $recharge_model->money_front = $awards->money;
                $recharge_model->money_after = $awards->money + $params['money'];
                $recharge_model->source_id = \Yii::$app->admin->id;
                if (!$recharge_model->save()) {
                    $transaction->rollBack();
                    throw new \Exception($this->responseErrorMsg($awards));
                }

                $awards->money = $awards->money + $params['money'];
                if (!$awards->save()) {
                    $transaction->rollBack();
                    throw new \Exception($this->responseErrorMsg($awards));
                }

                $transaction->commit();
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '充值成功'
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => $e->getMessage(),
                    'error' => [
                        'line' => $e->getLine()
                    ]
                ];
            }

        }
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '该奖池不存在！请联系技术人员'
        ];
    }

    //添加/修改用户
    public function userEdit ($params)
    {
        $model = new BossAwardMember();
        $time = time();
        $t = \Yii::$app->db->beginTransaction();
        try {
            if (isset($params['ids'])) {
                $condition = 'id in (' . implode(",", $params['ids']) . ')';
                $model->deleteAll( $condition);
            } else { //添加奖池用户
                if (!isset($params['award_id']) || !$params['award_id']) {
                    $t->rollBack();
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '请传入奖池ID'
                    ];
                }

                if (!isset($params['user_ids']) || !$params['user_ids']){
                    $t->rollBack();
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '请传入用户ID'
                    ];
                }

                $data = [];
                foreach ($params['user_ids'] as $value) {
                    $data[] = [
                        \Yii::$app->mall->id,
                        $params['award_id'],
                        $value,
                        $time,
                        $time,
                    ];
                }
                if ($data) {
                    $this->add_all(
                        $model::tableName(),
                        ['mall_id','award_id','user_id','created_at','updated_at'],
                        $data
                    );
                }
            }
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => isset($params['ids']) ? '移除成功' : '添加成功'
            ];
        } catch (\Exception $e) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => stripos($e->getMessage(),'plugin_boss_award_member_user_award_unique') ? '该用户已经添加，请勿重复' : $e->getMessage()
            ];
        }
    }

    public function add_all($table,$select,$add)
    {
        $connection = \Yii::$app->db;
        //数据批量入库
        $connection->createCommand()->batchInsert(
            $table,
            $select,//['series_name','series_turnover','created_at'],//字段
            $add
        )->execute();

    }

    //是否启用
    public function isEnable ($params)
    {
        if (isset($params['id']) && $params['id']) {
            $boss_awards = BossAwards::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $params['id']]);
            if (!$boss_awards) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '该奖池不存在'
                ];
            }
            $member_count = BossAwardMember::find()->where(['award_id' => $params['id'], 'mall_id' => \Yii::$app->mall->id])->count();

            if ($member_count <= 0 && !$boss_awards->level_id) {
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '请先添加股东！'
                ];
            }
            $boss_awards->status = $params['status'];
            $boss_awards->next_send_time = strtotime(date('Y-m-d')) + ($boss_awards->period * $this->computingTime($boss_awards->period_unit));

            if (!$boss_awards->save()) {
                throw new \Exception($this->responseErrorMsg($boss_awards));
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            }
        } else {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请传入ID'
            ];
        }
    }

    public function computingTime($type){
        switch ($type)
        {
            case 'day':
                $second = 24 * 60 * 60;
                break;
            case 'week':
                $second = 7 * 24 * 60 * 60;
                break;
            case 'month':
                $second = date("t") * 24 * 60 * 60;
                break;
            case 'year':
                $second = 365 * 24 * 60 * 60;
                break;
            default:
                $second = 0;
                break;
        }
        return $second;
    }

    //发放
    public function distribution ($id)
    {
        try {
            //查看奖金池
            $query = BossAwards::find();
            $boss_awards_data = $query->andWhere([
                "AND",
                ["id" => $id],
                ["is_delete" => 0],
            ])->asArray()->one();
            if(!$boss_awards_data)
                throw new \Exception('该奖金池不存在，请联系技术人员！');

            if(!$boss_awards_data['status'])
                throw new \Exception('奖金池未开启，请先开启。');

            $each_log_form = new BossAwardsEachLogForm();
            $sent_log_form = new BossAwardsSentLogForm();
            $boss_awards_model = new BossAwards();
            $time = time();
            $each_log_data = [];

            if ($boss_awards_data['money'] <= 0)
                throw new \Exception($boss_awards_data['name'] . "奖金池金额不足：" . date("Y-m-d H:i:s", $time));

            //查询当前奖池需要发放人员
            $boss_data = [];
            if ($boss_awards_data['level_id'] && is_string($boss_awards_data['level_id'])) {
                $level_ids = json_decode($boss_awards_data['level_id'],true);
                $boss_data = Boss::find()->select('id,user_id,level_id')
                    ->andWhere(['and', ['in','level_id',$level_ids], ['is_delete' => 0]])
                    ->asArray()
                    ->all();
            }

            $awards_member_data = BossAwardMember::find()->select('user_id')
                ->andWhere(['award_id' => $boss_awards_data['id']])
                ->asArray()->all();

            if (!$awards_member_data && !$boss_data)
                throw new \Exception($boss_awards_data['name'] . "奖金池暂无分红股东" . date("Y-m-d H:i:s", $time));

            $awards_member_data = array_merge_recursive($awards_member_data, $boss_data);

            $user_ids = array_unique(array_column($awards_member_data, 'user_id'));

            $price = $boss_awards_data['money'] * ($boss_awards_data['rate'] * 0.01);//计算分红金额

            $count_user = count($user_ids);//分红总人数

            $per_person = $price / $count_user;//每人分的钱

            $trans = \Yii::$app->db->beginTransaction();

            $next_time = date('Ymd', $boss_awards_data['next_send_time']);

            try {
                //查看是否是自动分红
                if ($boss_awards_data['automatic_audit']) {
                    //修改奖池金额
                    $award_res = $boss_awards_model->updateAll(['money' => $boss_awards_data['money'] - $price],['id' => $boss_awards_data['id']]);
                    if (!$award_res)
                        throw new \Exception($boss_awards_data['name'] . "修改奖池金额失败" . date("Y-m-d H:i:s", $time));

                    $each_log_data['actual_money'] = $price;
                    $each_log_data['money_after'] = $boss_awards_data['money'] - $price;
                } else {
                    $each_log_data['actual_money'] = 0;
                    $each_log_data['money_after'] = $boss_awards_data['money'];
                }
                //添加每期奖池记录
                $each_log_data = array_merge($each_log_data,[
                    "awards_cycle"      => $boss_awards_data['name'] . "第" . $next_time . '期',
                    "awards_id"         => $boss_awards_data['id'],
                    "money"             => $price,
                    "people_num"        => $count_user,
                    "money_front"       => $boss_awards_data['money'],
                    "rate"              => $boss_awards_data['rate'],
                    "sent_time"         => $next_time,
                ]);
                $each_res = $each_log_form->save($each_log_data);
                if (!isset($each_res['code']) || $each_res['code'])
                    throw new \Exception($boss_awards_data['name'] . "奖金池添加每期记录失败" . date("Y-m-d H:i:s", $time));

                //添加每人每期发放记录
                foreach ($user_ids as $sent_val)
                {
                    $sent_log_data = [
                        "each_id"       => $each_res['data'],
                        "user_id"       => $sent_val,
                        "money"         => $per_person,
                        "award_set"     => json_encode([
                            'money'         => $price,
                            'rate'          => $boss_awards_data['rate'],
                            'people_number' => $count_user,
                        ]),
                        "send_date"     => $next_time,
                    ];
                    if ($boss_awards_data['automatic_audit']) {
                        $sent_log_data['status'] = 1;
                        $sent_log_data['payment_time'] = $time;
                    }
                    $sent_res = $sent_log_form->save($sent_log_data);

                    if (!isset($sent_res['code']) || $sent_res['code'])
                        throw new \Exception($boss_awards_data['name'] . "奖金池". $sent_val ."用户添加记录失败" . date("Y-m-d H:i:s", $time));

                    if ($boss_awards_data['automatic_audit']) {
                        //修改用户金额
                        $user = User::findOne((int)$sent_val);
                        if (!$user || $user->is_delete)
                            throw new \Exception($boss_awards_data['name'] . "奖金池". $sent_val ."用户添加记录失败" . date("Y-m-d H:i:s", $time));

                        UserIncomeForm::bossAdd($user, $per_person, $sent_res['data'],'来自股东分红' . $boss_awards_data['name'] . "第" . $next_time . '期');

                        //修改股东总分红记录
                        $boss = Boss::findOne(['user_id' => $sent_val, 'is_delete' => 0]);
                        if(!$boss)
                            throw new \Exception($boss_awards_data['name'] . "股东不存在");

                        $boss->total_price = $boss->total_price + $per_person;
                        if (!$boss->save())
                            throw new \Exception($boss_awards_data['name'] . "奖金池" . $sent_val . "修改股东总分红记录失败");

                    }
                }

                //修改当前奖池日期
                BossAwards::updateAll([
                    'last_send_time' => $boss_awards_data['next_send_time'],
                    'next_send_time' => $boss_awards_data['next_send_time'] + ($boss_awards_data['period'] * $this->computingTime($boss_awards_data['period_unit']))
                ],
                    'id = ' . $boss_awards_data['id']);

            } catch (\Exception $e){
                $trans->rollBack();
                return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
            }
            $trans->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        } catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}