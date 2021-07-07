<?php
namespace app\mch\forms\api\apply;

use app\core\ApiCode;
use app\mch\forms\mch\EfpsReviewInfoForm;
use app\models\EfpsMchReviewInfo;

class MerchantTypeForm extends EfpsReviewInfoForm{

    public function save(){
        try {

            $this->checkData();

            return parent::save();
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

        if(empty($this->paper_merchantType) || !in_array($this->paper_merchantType, [ EfpsMchReviewInfo::MERCHANTTYPE_IND, EfpsMchReviewInfo::MERCHANTTYPE_ENT, EfpsMchReviewInfo::MERCHANTTYPE_PER])){
            throw new \Exception("请设置商户类型");
        }

        $this->paper_isCc = 1;

        if($this->paper_merchantType != EfpsMchReviewInfo::MERCHANTTYPE_PER){
            if(empty($this->paper_businessLicenseCode)){ //营业执照号
                throw new \Exception("请设置营业执照号");
            }
            if(empty($this->paper_businessLicenseName)){ //商户经营名称
                throw new \Exception("请设置商户经营名称");
            }
            if(empty($this->paper_businessLicensePhoto)){ //营业执照照片
                throw new \Exception("请设置营业执照照片");
            }
            if(empty($this->paper_businessLicenseTo)){ //营业执照有效期（截止）
                throw new \Exception("请设置营业执照有效期（截止）");
            }
            if(empty($this->paper_lawyerName)){ //法人姓名
                throw new \Exception("请设置法人姓名");
            }
            if(empty($this->paper_businessScope)){ //经营范围
                throw new \Exception("请设置经营范围");
            }
            if(empty($this->paper_registerAddress)){ //注册地址
                throw new \Exception("请设置注册地址");
            }
        }

        if(empty($this->paper_shortName)){ //商户简称
            throw new \Exception("请设置商户简称");
        }
    }
}