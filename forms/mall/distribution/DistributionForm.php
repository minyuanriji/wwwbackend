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
use app\events\DistributionMemberEvent;
use app\forms\common\QrCodeCommon;
use app\handlers\HandlerRegister;
use app\models\BaseModel;
use app\models\Distribution;
use app\models\User;

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
        $distribution->first_children = 0;
        $distribution->all_children = 0;
        $distribution->is_delete = 1;
        $distribution->reason = $this->reason;
        $distribution->delete_first_show = 0;
        $distribution->deleted_at = time();
        if ($distribution->save()) {
            $user = User::findOne(['id' => $distribution->user_id]);
            $user->is_distributor = 0;
            $parentId = $distribution->user->parent_id;
            if ($user->save()) {
                /*User::updateAll(
                    ['parent_id' => 0],
                    ['or',
                        ['parent_id' => $distribution->user_id],
                        ['user_id' => $distribution->user_id]
                    ]
                );*/
                $t->commit();
                \Yii::$app->trigger(HandlerRegister::CHANGE_PARENT, new DistributionMemberEvent([
                    'mall' => \Yii::$app->mall,
                    'beforeParentId' => $parentId,
                    'parentId' => 0,
                    'userId' => $distribution->user_id
                ]));
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

    public function getQrcode()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $distribution = Distribution::findOne(['id' => $this->id, 'is_delete' => 0, 'mall_id' => \Yii::$app->mall->id]);

            if (!$distribution) {
                throw new \Exception('分销商不存在');
            }
            $form = new QrCodeCommon();
            $form->appPlatform = 'all';
            $list = $form->getQrCode(['user_id' => $distribution->user_id]);
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => $list
            ];
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }
}
