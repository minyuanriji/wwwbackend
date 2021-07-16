<?php

namespace app\plugins\mch\forms\mall;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\Admin;
use app\models\DistrictArr;
use app\models\BaseModel;
use app\models\EfpsMerchantMcc;
use app\models\EfpsMchReviewInfo;
use app\models\User;
use app\plugins\mch\forms\common\CommonMchForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchApplyOperationLog;

class MchExamineLogForm extends BaseModel
{
    public $keyword;
    public $page;

    const operation = [
        1 => '通过',
        2 => '拒绝',
        3 => '特殊申请',
    ];

    public function rules()
    {
        return [
            [['keyword',], 'string'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorMsg();
        }

        $query = MchApplyOperationLog::find()->select([
            '*',
            "DATE_FORMAT(FROM_UNIXTIME(created_at),'%Y-%m-%d %H:%i:%s') as created_at"
        ])->where([
            'mall_id' => \Yii::$app->mall->id,
        ]);

        if ($this->keyword) {
            $mchApplyIds = MchApply::find()->where(['like', 'json_apply_data', $this->keyword])->select('id')->asArray()->all();
            $userIds = User::find()->where(['like', 'nickname', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->select('id')->asArray()->all();
            $adminUserIds = Admin::find()->where(['like', 'username', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->select('id')->asArray()->all();
            if ($adminUserIds && $adminUserIds) {
                $new_user_id = array_unique(array_column(array_merge_recursive($userIds, $adminUserIds), 'id'));
            } else {
                $new_user_id = $adminUserIds ? array_column($adminUserIds, 'id') : array_column($userIds, 'id');
            }
            $query->andWhere([
                'or',
                ['in', 'mch_apply_id', $mchApplyIds ? array_column($mchApplyIds, 'id') : ''],
                ['in', 'user_id', $new_user_id]
            ]);
        }

        $query->with('mchApply');
        $list = $query->orderBy(['created_at' => SORT_DESC])->page($pagination)->asArray()->all();
        if ($list) {
            foreach ($list as &$item) {
                if ($item['mchApply']) {
                    if ($item['mchApply']['json_apply_data']) {
                        $item['store_name'] = json_decode($item['mchApply']['json_apply_data'], true)['store_name'];
                    } else {
                        $item['store_name'] = '';
                    }
                } else {
                    $item['store_name'] = '';
                }
                if ($item['operation_terminal'] == MchApplyOperationLog::OPERATION_TERMINAL_RECEPTION) {
                    $item['operation_terminal_type'] = '前台';
                    $user = User::findOne($item['user_id']);
                    $item['nickname'] = $user ? $user->nickname : '';
                } else {
                    $item['operation_terminal_type'] = '后台';
                    $admin = Admin::findOne($item['user_id']);
                    $item['nickname'] = $admin ? $admin->username : '';
                }
                $item['operation_status'] = self::operation[$item['operation']];
                unset($item['mchApply'], $item['operation'],  $item['operation_terminal'],);
            }
        }
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}
