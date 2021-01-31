<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 19:30
 */

namespace app\plugins\distribution\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\User;
use app\plugins\distribution\models\Distribution;

class DistributionForm extends BaseModel
{

    public $id;
    public $reason;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['reason'], 'string'],
            [['reason'], 'trim'],
        ];
    }

    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        /* @var Distribution $distribution */
        $distribution = Distribution::find()->with(['userInfo'])
            ->where(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id])->one();

        if (!$distribution) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => '分销商不存在'
            ];
        }

        $t = \Yii::$app->db->beginTransaction();

        $distribution->is_delete = 1;
        $distribution->delete_reason = $this->reason;

        $distribution->deleted_at = time();
        if ($distribution->save()) {
            $user = User::findOne(['id' => $distribution->user_id]);
            $user->is_inviter = 0;
            $parentId = $distribution->user->parent_id;
            if ($user->save()) {
                User::updateAll(
                    ['parent_id' => 0],
                    ['or',
                        ['parent_id' => $distribution->user_id],
                        ['user_id' => $distribution->user_id]
                    ]
                );
                $t->commit();
         /*       \Yii::$app->trigger(HandlerRegister::CHANGE_DISTRIBUTION_MEMBER, new DistributionMemberEvent([
                    'mall' => \Yii::$app->mall,
                    'beforeParentId' => $parentId,
                    'parentId' => 0,
                    'userId' => $score->user_id
                ]));*/
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '删除成功'
                ];
            } else {
                $t->rollBack();
                return $this->responseErrorInfo($user);
            }
        } else {
            $t->rollBack();
            return $this->responseErrorInfo($distribution);
        }

    }

}