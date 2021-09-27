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

class BossAwardsExamineDeleteForm extends BaseModel
{
    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    //刪除
    public function doDelete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $BossAward = BossAwardSentLog::find()->where(['id' => $this->id])->one();

        if (!$BossAward)
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '数据异常,该条数据不存在');

        try {
            $res = $BossAward->delete();
            if (!$res)
                throw new \Exception($this->responseErrorMsg($BossAward));

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage(), [
                'error' => [
                    'line' => $e->getLine()
                ]
            ]);
        }
    }
}