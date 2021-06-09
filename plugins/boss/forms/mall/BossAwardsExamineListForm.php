<?php

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\forms\common\UserIncomeForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardEachLog;
use app\plugins\boss\models\BossAwardMember;
use app\plugins\boss\models\BossAwards;
use app\plugins\boss\models\BossAwardSentLog;

class BossAwardsExamineListForm extends BaseModel
{
    public $keyword;
    public $page;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['page', ], 'integer'],
            [['page'], 'default', 'value' => 1]
        ];
    }

    //查看
    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $sent_list = BossAwardSentLog::find()
            ->with(['bossAwardEachLog' => function ($query) {
                $query->select('id,awards_cycle');
            }])
            ->with(['user' => function ($query) {
                $query->select('id,nickname,mobile')
                    ->keyword($this->keyword, ['or',['like', 'nickname', $this->keyword],['like', 'mobile', $this->keyword]]);
            }])
            ->page($pagination, 20, $this->page)
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all();
        if ($sent_list) {
            foreach ($sent_list as $key => $value) {
                if ($value['bossAwardEachLog']) {
                    $sent_list[$key]['awards_cycle'] = $value['bossAwardEachLog'][0]['awards_cycle'];
                    unset($sent_list[$key]['bossAwardEachLog']);
                } else {
                    $sent_list[$key]['awards_cycle'] = '';
                }
                if ($value['user']) {
                    $sent_list[$key]['nickname'] = $value['user'][0]['nickname'];
                    $sent_list[$key]['mobile'] = $value['user'][0]['mobile'];
                    unset($sent_list[$key]['user']);
                } else {
                    $sent_list[$key]['nickname'] = '';
                    $sent_list[$key]['mobile'] = '';
                }
            }
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'list' => $sent_list,
                'pagination' => $pagination
            ]
        ];
    }

    //审核
    public function examine($id)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            //修改状态
            $sent = BossAwardSentLog::findOne(['id' => $id, 'status' => 0]);
            if ($sent) {
                $sent->status = 1;
                $sent->payment_time = time();
                if (!$sent->save()) {
                    $t->rollBack();
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,(new BaseModel())->responseErrorMsg($sent));
                }
                $each_log = new BossAwardsEachLogForm();

                //修改该期奖池金额
                $each_res = BossAwardEachLog::find()->andWhere(['id' => $sent->each_id])->one();
                if (!$each_res) {
                    $t->rollBack();
                    throw new \Exception($this->responseErrorMsg($each_res));
                }
                $each_save_res = $each_log->save([
                    'id' => $sent->each_id,
                    'actual_money' => $each_res->actual_money + $sent->money,
                    'money_after' => $each_res->money_after - $sent->money,
                ]);
                if (!$each_save_res) {
                    $t->rollBack();
                    throw new \Exception($this->responseErrorMsg($each_save_res));
                }

                //修改奖池金额
                $awards_res = BossAwards::find()->andWhere(['AND',['is_delete' => 0],['id' => $each_res->awards_id]])->one();
                if (!$awards_res) {
                    $t->rollBack();
                    throw new \Exception($this->responseErrorMsg($awards_res));
                }
                $awards_res->money = $awards_res->money - $sent->money;
                if (!$awards_res->save()) {
                    $t->rollBack();
                    throw new \Exception($this->responseErrorMsg($awards_res));
                }

                //修改用户金额
                $user = User::findOne((int)$this->user_id);
                if(!$user || $user->is_delete){
                    $t->rollBack();
                    throw new \Exception("用户不存在");
                }
                UserIncomeForm::bossAdd($user, $sent->money, $id);

                //修改股东总分红记录
                $boss = Boss::findOne(['user_id' => $sent->user_id, 'is_delete' => 0]);
                if(!$boss){
                    $t->rollBack();
                    throw new \Exception("股东不存在");
                }
                $boss->total_price = $boss->total_price + $sent->money;
                if (!$boss->save()) {
                    $t->rollBack();
                    return $this->returnApiResultData(ApiCode::CODE_FAIL,(new BaseModel())->responseErrorMsg($boss));
                }
                $t->commit();
                return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '打款成功');
            }
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '该记录不存在或状态不对，请联系技术人员！');
        } catch (\Exception $exception) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $exception->getMessage());
        }
    }

    //查看详情
    public function getDetail($id)
    {
        $level = BossAwards::findOne(['id' => $id, 'is_delete' => 0]);
        if ($level) {
            $level->period_unit = array_search($level->period_unit,$this->period_type_change);
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
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请传入id'
            ];
        }
        $level = BossAwards::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $id]);
        if ($level) {
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
        if (\Yii::$app->admin->id != 148) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '请联系夏文充值！'
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
        $level = BossAwards::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $params['id']]);
        if ($level) {
            $level->money = $params['money'];
            if (!$level->save()) {
                throw new \Exception($this->responseErrorMsg($level));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '充值成功'
            ];
        }
        return [
            'code' => ApiCode::CODE_FAIL,
            'msg' => '该奖池不存在！请联系技术人员'
        ];
    }

    //添加/修改用户
    public function userEdit ($params)
    {
        //删除该奖池某用户
        $model = new BossAwardMember();
        $time = time();
        $t = \Yii::$app->db->beginTransaction();
        if (isset($params['ids']) && is_string($params['ids'])) {
            $ids = json_decode($params['ids'],true);
            $condition = 'id in (' . implode(",", $ids) . ')';
            $model->deleteAll( $condition);
        } else { //添加奖池用户
            if (!isset($params['award_id']) || !$params['award_id']) {
                $t->rollBack();
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '请传入奖池ID'
                ];
            }

            if (!isset($params['user_ids']) || !is_string($params['user_ids']) || !$params['user_ids']){
                $t->rollBack();
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '请传入用户ID'
                ];
            }

            $user_ids = json_decode($params['user_ids'],true);
            $data = [];
            foreach ($user_ids as $value) {
                $data[] = [
                    \Yii::$app->mall->id,
                    $params['award_id'],
                    $value,
                    $time,
                    $time,
                    0,
                ];
            }
            if (isset($data)) {
                $this->add_all(
                    $model::tableName(),
                    ['mall_id','award_id','user_id','created_at','updated_at','deleted_at'],
                    $data
                );
            }
        }
        $t->commit();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => isset($params['ids']) && is_string($params['ids']) ? '移除成功' : '添加成功'
        ];
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

}