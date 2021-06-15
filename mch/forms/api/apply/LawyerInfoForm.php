<?php
namespace app\mch\forms\api\apply;

use app\core\ApiCode;
use app\mch\forms\mch\EfpsReviewInfoForm;
use app\models\EfpsMchReviewInfo;
use app\plugins\mch\models\Mch;

class LawyerInfoForm extends EfpsReviewInfoForm{

    public function save(){
        try {

            $this->checkData();

            $res = parent::save();

            if($res['code'] == ApiCode::CODE_SUCCESS){
                EfpsMchReviewInfo::updateAll([
                    "status" => 1
                ], ["mch_id" => $this->mch_id]);
                Mch::updateAll([
                    "review_status" => Mch::REVIEW_STATUS_UNCHECKED
                ], ["id" => $this->mch_id]);
            }

            return $res;
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

    private function checkData(){
        $this->paper_lawyerCertType = 0; //固定为身份证

        if(empty($this->paper_lawyerCertNo)){ //证件号码
            throw new \Exception("请设置证件号码");
        }

        if(empty($this->paper_lawyerCertPhotoFront)){ //证件正面照
            throw new \Exception("请设置证件正面照");
        }

        if(empty($this->paper_lawyerCertPhotoBack)){ //证件背面照
            throw new \Exception("请设置证件背面照");
        }

        if(empty($this->paper_certificateName)){ //证件人姓名
            throw new \Exception("请设置证件人姓名");
        }

        if(empty($this->paper_certificateTo)){ //证件有效期(截止)
            throw new \Exception("请设置证件有效期(截止)");
        }
    }
}