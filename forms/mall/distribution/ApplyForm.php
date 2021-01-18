<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销商
 * Author: zal
 * Date: 2020-04-16
 * Time: 10:45
 */

namespace app\forms\mall\distribution;

use app\core\ApiCode;
use app\forms\common\distribution\DistributionCommon;
use app\models\BaseModel;
use app\models\User;

class ApplyForm extends BaseModel
{
    public $user_id;
    public $status;
    public $reason;

    public function rules()
    {
        return [
            [['user_id', 'status'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['reason'], 'trim'],
            [['reason'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        /* @var User $user */
        $user = User::find()->with('share')
            ->where(['id' => $this->user_id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])
            ->one();
        if (!$user) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '分销商不存在'
            ];
        }
        if (!$user->share) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '分销商不存在'
            ];
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            $commonDistribution = DistributionCommon::getCommon();
            $commonDistribution->becomeDistribution($user, [
                'status' => $this->status,
                'reason' => $this->reason
            ]);
            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '处理成功'
            ];
        } catch (\Exception $exception) {
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
