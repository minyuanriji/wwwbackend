<?php

namespace app\forms\api\boss;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\boss\forms\mall\BossAwardsListForm;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossAwardSentLog;

class BonusListForm extends BaseModel
{
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10],
        ];
    }

    /**
     * 分红明细
     * @return array
     */
    public function details()
    {
        try {
            if (\Yii::$app->user->isGuest) {
                throw new \Exception('用户未登录。');
            }

            $return_data = [];
            //获取奖金池
            $awards_form = new BossAwardsListForm();
            $awards_res = $awards_form->search(['status' => 1],'id,created_at,money,name');
            if (!isset($awards_res['code']) || $awards_res['code']) {
                throw new \Exception(isset($awards_res['msg']) ? $awards_res['msg'] : '异常错误');
            }
            $return_data['awards_list'] = isset($awards_res['data']['list']) ? $awards_res['data']['list'] : [];

            $user = \Yii::$app->user->identity;
            $boss_res = Boss::find()
                ->select('id,user_id,total_price,level_id')
                ->andWhere(['user_id' => $user->id, 'is_delete' => 0])
                ->with(['bossLevel' => function ($query) {
                    $query->select('id,name');
                }])
                ->asArray()
                ->one();
            if ($boss_res) {
                if (isset($boss_res['bossLevel']) && $boss_res['bossLevel']) {
                    $boss_res['level_name'] = $boss_res['bossLevel'][0]['name'];
                    unset($boss_res['bossLevel']);
                } else {
                    $boss_res['level_name'] = '';
                }
                $boss_res['avatar_url'] = $user->avatar_url;
                $return_data['user_bonus'] = $boss_res;
                unset($boss_res);
                //个人分红
                $boss_sent_log = BossAwardSentLog::find()
                    ->select('id,each_id,money,payment_time')
                    ->with(['bossAwardEachLog' => function ($query) {
                        $query->select('id,awards_cycle');
                    }])
                    ->andWhere(['status' => 1, 'user_id' => $user->id])
                    ->asArray()
                    ->page($pagination, $this->limit, $this->page)
                    ->orderBy(['id' => SORT_DESC])
                    ->all();

                if ($boss_sent_log) {
                    foreach ($boss_sent_log as $key => $value) {
                        if (isset($value['bossAwardEachLog']) && $value['bossAwardEachLog']) {
                            $boss_sent_log[$key]['awards_cycle'] = $value['bossAwardEachLog'][0]['awards_cycle'];
                        } else {
                            $boss_sent_log[$key]['awards_cycle'] = '';
                        }
                        unset($boss_sent_log[$key]['bossAwardEachLog']);
                        $boss_sent_log[$key]['payment_time'] = date('Y-m-d H:i:s', $value['payment_time']);
                    }
                    $return_data['bonus_log'] = $boss_sent_log;
                    unset($boss_sent_log);
                } else {
                    $return_data['bonus_log'] = [];
                }
            } else {
                $return_data['bonus_log'] = [];
                $return_data['user_bonus'] = [];
            }
            
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'list' => $return_data,
                    'pagination' => $pagination
                ]
            ];

        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}
