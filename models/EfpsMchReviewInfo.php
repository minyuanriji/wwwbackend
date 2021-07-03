<?php
namespace app\models;


class EfpsMchReviewInfo extends BaseActiveRecord {

    const MERCHANTTYPE_IND          = 1; //个体
    const MERCHANTTYPE_ENT          = 2; //企业
    const MERCHANTTYPE_PER          = 3; //个人

    const SETTLEACCOUNTTYPE_ENT     = 1; //对公
    const SETTLEACCOUNTTYPE_PER     = 2; //法人
    const SETTLEACCOUNTTYPE_AU_ENT  = 3; //授权对公
    const SETTLEACCOUNTTYPE_AU_PER  = 4; //授权对私


    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%efps_mch_review_info}}';
    }

    public function rules(){
        return array_merge(parent::rules(), [
            [["mch_id"], "required"],
            [["mch_id", "status", "created_at", "updated_at", "deleted_at", "is_delete",
                "openAccount", "acceptOrder", "paper_isCc", "paper_province",
                "paper_city", "paper_mcc", "paper_merchantType", "paper_lawyerCertType",
                "paper_settleAccountType", "paper_stage_feeRate", "paper_stage_feePer"], "integer"],
            [["acqMerId", "merchantName", "paper_businessLicenseCode",
                "paper_businessLicenseName", "paper_businessLicensePhoto",
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

}