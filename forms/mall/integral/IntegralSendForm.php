<?php

namespace app\forms\mall\integral;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\CouponAutoSend;
use app\models\Coupon;
use app\models\Integral;
use app\models\User;

class IntegralSendForm extends BaseModel
{
    public $page;
    public $page_size;

    public $id;
    public $mall_id;
    public $controller_type;
    public $user_id;
    public $integral_num;
    public $period;
    public $period_unit;
    public $type;
    public $effective_days;
    public $next_publish_time;
    public $parent_name;

    public $keyword;

    public function rules()
    {
        return [
            [['id', 'mall_id', 'controller_type', 'user_id', 'integral_num', 'period', 'period_unit', 'type', 'effective_days', 'next_publish_time'], 'integer'],
            [['keyword'], 'string'],
            [['page'], 'default', 'value' => 1],
            [['page_size'], 'default', 'value' => 10],
            [['parent_name'], 'safe'],
        ];
    }

    /**
     * 获取列表
     * @return array
     */
    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $query = Integral::find()->alias('i')->where([
            'i.mall_id' => \Yii::$app->mall->id,
        ]);

        $query->joinwith(['user' => function ($query) {
            if ($this->keyword) {
                $query->where(['like', 'nickname', $this->keyword]);
            }
            $query->andWhere(['is_delete' => 0]);
        }]);

        $list = $query
            ->page($pagination, $this->page_size, $this->page)
            ->orderBy('i.id DESC')
            ->asArray()
            ->all();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'list' => $list,
                'pagination' => $pagination
            ]
        ];
    }

    /**
     * 删除
     * @return array
     */
    public function destroy()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $model = CouponAutoSend::findOne([
            'id' => $this->id,
            'mall_id' => \Yii::$app->mall->id,
            'is_delete' => 0
        ]);
        if (!$model) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '数据不存在或已删除',
            ];
        }
        $model->is_delete = 1;
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->save();
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '删除成功'
        ];
    }

    /**
     * 详情
     * @return array
     */
    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $list = CouponAutoSend::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'id' => $this->id
        ])->one();
        if ($list) {
            $userIdList = $list->user_list ? json_decode($list->user_list, true) : [];
            $userList = User::find()->where(['id' => $userIdList, 'mall_id' => \Yii::$app->mall->id])
                ->with('userInfo')->all();
            $newUserList = [];
            /* @var User[] $userList */
            foreach ($userList as $user) {
                $newUserList[] = [
                    'user_id' => $user->id,
                    'nickname' => $user->nickname,
                    'avatar' => $user->userInfo->avatar,
                    'platform' => $user->userInfo->platform
                ];
            }

            $list->user_list = $newUserList;
        }
        $coupon_list = Coupon::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'data' => [
                'coupon_list' => $coupon_list,
                'list' => $list,
            ]
        ];
    }

    /**
     * 保存
     * @return array
     */
    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $model = new Integral();
        $model->mall_id = \Yii::$app->mall->id;
        $model->user_id = $this->user_id;
        $model->integral_num = $this->integral_num;
        $model->period = $this->period;
        $model->period_unit = $this->period_unit == 1 ? 'month' : 'week';
        $model->type = $this->type;
        $model->effective_days = $this->effective_days;
        $model->next_publish_time = $this->next_publish_time;
        $model->desc = '后台手动添加积分计划';
        $model->parent_id = \Yii::$app->admin->id;
        $model->source_type = 'admin_manual';
        if ($model->save()) {
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } else {
            return $this->responseErrorInfo($model);
        }
    }
}
