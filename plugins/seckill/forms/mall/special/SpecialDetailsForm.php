<?php

namespace app\plugins\seckill\forms\mall\special;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\seckill\models\Seckill;

class SpecialDetailsForm extends BaseModel
{

    public $seckill_id;

    public function rules()
    {
        return [
            [['seckill_id'], 'integer'],
        ];
    }

    public function getDetail()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $detail = Seckill::findOne($this->seckill_id);
            if ($detail) {
                $detail->start_time = date('Y-m-d H:i:s', $detail->start_time);
                $detail->end_time = date('Y-m-d H:i:s', $detail->end_time);
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', $detail);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}