<?php
namespace app\mch\forms\api\apply;

use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\mch\forms\mch\EfpsReviewInfoForm;
use app\models\EfpsMchReviewInfo;
use app\plugins\mch\models\Mch;

class QueryStatusForm extends EfpsReviewInfoForm{

    public function query(){
        try {
            $mch = Mch::findOne([
                "id"        => $this->mch_id,
                "is_delete" => 0
            ]);
            if(!$mch){
                throw new \Exception("商户不存在");
            }

            $status = 0;
            $relatEfps = EfpsMchReviewInfo::findOne(["mch_id" => $this->mch_id]);
            if($relatEfps){
                $status = $relatEfps['status'];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'status' => $status,
                    'remark' => $mch->review_remark,
                    'detail' => ArrayHelper::toArray($relatEfps)
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }

}