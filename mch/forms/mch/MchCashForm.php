<?php

namespace app\mch\forms\mch;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;

class MchCashForm extends BaseModel
{
    public $mch_id;

    public function rules(){
        return [
            [['mch_id'], 'required'],
        ];
    }

    public function getList ()
    {
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {
            $mch = Mch::findOne($this->mch_id);
            if(!$mch || $mch->review_status != Mch::REVIEW_STATUS_CHECKED || $mch->is_delete){
                throw new \Exception("商户不存在");
            }

            $mch_cash_list = MchCash::find()
                ->select([
                    "id",
                    "mall_id",
                    "mch_id",
                    "content",
                    "fact_price",
                    "status",
                    "transfer_status",
                    "DATE_FORMAT(FROM_UNIXTIME(created_at),'%Y-%m-%d %H:%i:%s') as created_at",
                ])
                ->where([
                    'id'        => $this->mch_id ? $this->mch_id : \Yii::$app->mchAdmin->identity->mch_id,
                    'mall_id'   => $mch->mall_id,
                    'is_delete' => 0,
                ])
                ->asArray()->all();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' =>$mch_cash_list
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
            ];
        }
    }

}
