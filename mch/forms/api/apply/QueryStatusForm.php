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

    public function upQuery(){
        try {
            $transaction = \Yii::$app->db->beginTransaction();

            $mch = Mch::findOne([
                "id"        => $this->mch_id,
                "is_delete" => 0,
                "review_status" => 2,
            ]);
            if(!$mch){
                $transaction->rollBack();
                throw new \Exception("商户状态错误");
            }

            $mch->review_status = 0;
            $res = $mch->save();
            if (!$res) {
                $transaction->rollBack();
                throw new \Exception($this->responseErrorMsg($mch));
            }

            $relatEfps = EfpsMchReviewInfo::findOne(["mch_id" => $this->mch_id, 'is_delete' => 0, 'status' => 3]);
            if(!$relatEfps){
                $transaction->rollBack();
                throw new \Exception("商户资料状态错误");
            }

            $relatEfps->status = 0;
            $relat_res = $relatEfps->save();
            if (!$relat_res) {
                $transaction->rollBack();
                throw new \Exception($this->responseErrorMsg($relat_res));
            }
            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                ]
            ];
        }catch (\Exception $e){
            $transaction->rollBack();
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