<?php

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardMember;
use app\plugins\boss\models\BossAwards;

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
        $list = BossAwards::find()->where([
            'mall_id' => \Yii::$app->mall->id, 'is_delete' => 0
        ])->keyword($this->keyword, ['or',['like', 'name', $this->keyword],['like', 'award_sn', $this->keyword]])
            ->page($pagination, 20, $this->page)->orderBy(['id' => SORT_DESC])->all();

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
                $new_value['created_at'] = date('Y-m-d H:i:s', $value->created_at);
                $new_list[] = $new_value;
            }
        }
        unset($new_value);
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
            if (isset($post_data['id'])) {
                $level = BossAwards::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $post_data['id']]);
                if (!$level) {
                    $level = new BossAwards();
                    $level->mall_id = \Yii::$app->mall->id;
                    $level->award_sn = 'BSH' . $top_sn;
                } else {
                    if ($level->status == 1 && $post_data['status'] != 2) {
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => '请先关闭奖池在修改'
                        ];
                    }
                }
            } else {
                $level = new BossAwards();
                $level->mall_id = \Yii::$app->mall->id;
                $level->award_sn = 'BSH' . $top_sn;
            }
            $level->name = $post_data['name'];
            $level->status = $post_data['status'];
            $level->period = $post_data['period'];
            $level->period_unit = $post_data['period_unit'];
            $level->rate = $post_data['rate'];
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

}