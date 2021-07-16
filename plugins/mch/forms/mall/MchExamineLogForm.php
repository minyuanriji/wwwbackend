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
            $mchApplyIds = MchApply::find()->where(['like', 'json_apply_data', $this->keyword])->select('id');
            $userIds = User::find()->where(['like', 'nickname', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->select('id');
            $adminUserIds = Admin::find()->where(['like', 'username', $this->keyword])->andWhere(['mall_id' => \Yii::$app->mall->id])->select('id');
            $query->andWhere([
                ['mch_apply_id' => $mchApplyIds],
                'or',
                ['user_id' => array_merge($adminUserIds, $userIds)],
            ]);
        }

        $query->with('mchApply');
        $list = $query->orderBy(['created_at' => SORT_DESC])->page($pagination)->asArray()->all();
        if ($list) {
            foreach ($list as &$item) {
                if ($item['mchApply']) {

                }
            }
        }

        print_r($list);die;
        return [
            'list' => $list,
            'pagination' => $pagination
        ];
    }
}
