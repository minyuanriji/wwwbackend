<?php
namespace app\mch\forms\mch;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;

class EfpsReviewInfoForm extends BaseModel {

    public $mch_id;
    public $acqMerId;
    public $merchantName;
    public $acceptOrder;
    public $openAccount;
    public $paper_merchantType;
    public $paper_businessLicenseCode;
    public $paper_businessLicenseName;
    public $paper_businessLicensePhoto;
    public $paper_businessLicenseTo;
    public $paper_shortName;
    public $paper_isCc;
    public $paper_lawyerName;
    public $paper_businessScope;
    public $paper_registerAddress;
    public $paper_organizationCode;
    public $paper_organizationCodePhoto;
    public $paper_organizationCodeFrom;
    public $paper_organizationCodeTo;
    public $paper_businessAddress;
    public $paper_province;
    public $paper_city;
    public $paper_mcc;
    public $paper_unionShortName;
    public $paper_storeHeadPhoto;
    public $paper_storeHallPhoto;
    public $paper_lawyerCertType;
    public $paper_lawyerCertNo;
    public $paper_lawyerCertPhotoFront;
    public $paper_lawyerCertPhotoBack;
    public $paper_certificateName;
    public $paper_certificateTo;
    public $paper_contactPerson;
    public $paper_contactPhone;
    public $paper_serviceTel;
    public $paper_email;
    public $paper_licenceAccount;
    public $paper_licenceAccountNo;
    public $paper_licenceOpenBank;
    public $paper_licenceOpenSubBank;
    public $paper_openingLicenseAccountPhoto;
    public $paper_settleAccountType;
    public $paper_settleAccountNo;
    public $paper_settleAccount;
    public $paper_settleTarget;
    public $paper_settleAttachment;
    public $paper_openBank;
    public $paper_openSubBank;
    public $paper_openBankCode;
    public $paper_businessCode;
    public $paper_settleCycle;
    public $paper_stage_feeRate;
    public $paper_stage_feePer;
    public $paper_stage_amountFrom;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    public $is_delete;
    public $status;

    public function rules(){
        return array_merge(parent::rules(), [
            [["mch_id"], "required"],
            [["mch_id", "status", "created_at", "updated_at", "deleted_at", "is_delete",
              "openAccount", "acceptOrder", "paper_isCc", "paper_province",
              "paper_city", "paper_mcc", "paper_merchantType", "paper_lawyerCertType",
              "paper_settleAccountType", "paper_stage_feeRate", "paper_stage_feePer"], "integer"],
            [["acqMerId", "merchantName", "paper_businessLicenseCode", "paper_businessLicenseName", "paper_businessLicensePhoto",
              "paper_businessLicenseTo", "paper_shortName",
              "paper_lawyerName", "paper_businessScope", "paper_registerAddress",
              "paper_organizationCode", "paper_organizationCodePhoto",
              "paper_organizationCodeFrom", "paper_organizationCodeTo",
              "paper_businessAddress", "paper_unionShortName", "paper_storeHeadPhoto",
              "paper_storeHallPhoto", "paper_lawyerCertNo", "paper_lawyerCertPhotoFront",
              "paper_lawyerCertPhotoBack", "paper_certificateName",
              "paper_certificateTo", "paper_contactPerson", "paper_contactPhone",
              "paper_serviceTel", "paper_email", "paper_licenceAccount",
              "paper_licenceAccountNo", "paper_licenceOpenBank",
              "paper_licenceOpenSubBank", "paper_openingLicenseAccountPhoto",
              "paper_settleAccountNo", "paper_settleAccount", "paper_settleTarget",
              "paper_settleAttachment", "paper_openBank", "paper_openSubBank",
              "paper_openBankCode", "paper_businessCode", "paper_settleCycle",
              "paper_stage_amountFrom"], 'safe']
        ]);
    }

    public function save(){

        if (!$this->validate()) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $this->responseErrorMsg()
            ];
        }

        try {
            $model = EfpsMchReviewInfo::findOne(["mch_id" => $this->mch_id]);

            if(!$model){
                $model = new EfpsMchReviewInfo();
                $model->mch_id     = $this->mch_id;
                $model->created_at = time();
                $model->updated_at = time();
            }
            $validAttributes = $model->attributes;
            foreach($this->attributes as $field => $value){
                if((!is_numeric($value) && empty($value))
                        || !in_array($field, $validAttributes, true))
                    continue;
                $model->$field = $value;
            }
            if(!$model->save()){
                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg'  => $this->responseErrorMsg($model)
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS
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