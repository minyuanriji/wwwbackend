<?php
namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\models\Store;
use app\models\User;
use app\plugins\mch\forms\api\MchApplyOperationLogSaveForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchApplyOperationLog;

class MchApplyPassForm extends BaseModel{

    public $id;
    public $bind_mobile;


    public function rules(){
        return [
            [['id', 'bind_mobile'], 'required']
        ];
    }

    public function save ($operation_terminal = MchApplyOperationLog::OPERATION_TERMINAL_BACKSTAGE)
    {
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $trans = \Yii::$app->getDb()->beginTransaction();
        try {
            $applyModel = MchApply::findOne($this->id);
            if (!$applyModel) {
                throw new \Exception("申请记录不存在", ApiCode::CODE_FAIL);
            }

            if ($applyModel->status != "verifying") {
                throw new \Exception("非审核中状态无法操作", ApiCode::CODE_FAIL);
            }

            //绑定手机号唯一值检查
            $existsMobile = Mch::find()->andWhere([
                "AND",
                ["mobile" => $this->bind_mobile],
                "user_id <> '".$applyModel->user_id."'"
            ])->exists();
            if($existsMobile){
                throw new \Exception("手机“".$this->bind_mobile."”已被其它商户绑定", ApiCode::CODE_FAIL);
            }

            $user = User::findOne($applyModel->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取到用户信息", ApiCode::CODE_FAIL);
            }


            //生成主商户记录
            $mch = $this->saveMch($applyModel, $user);

            //保存门店数据
            $this->saveStore($applyModel, $mch);

            //保存易票联信息
            $this->saveEfps($applyModel, $mch);

            $applyModel->status     = "passed";
            $applyModel->updated_at = time();
            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel), ApiCode::CODE_FAIL);
            }

            if ($operation_terminal == MchApplyOperationLog::OPERATION_TERMINAL_BACKSTAGE) {
                $user_id = \Yii::$app->admin->id;
            } else {
                $user_id = \Yii::$app->user->id;
            }
            $operation_save = MchApplyOperationLogSaveForm::addOperationLog($applyModel->mall_id, $applyModel->id, $operation_terminal, $user_id, MchApplyOperationLog::OPERATION_PASSED);
            if ($operation_save['code'] != ApiCode::CODE_SUCCESS)
                throw new \Exception(isset($operation_save['msg']) ? $operation_save['msg'] : $operation_save['message'], ApiCode::CODE_FAIL);

            $trans->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $trans->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 保存主商户记录信息
     * @param MchApply $applyModel
     * @param User $user
     * @return Mch|null
     * @throws \Exception
     */
    private function saveMch(MchApply $applyModel, User $user){
        $mch = Mch::findOne([
            "user_id" => $applyModel->user_id,
            "mall_id" => $applyModel->mall_id
        ]);
        if(!$mch){
            $mch = new Mch([
                "mall_id" => $applyModel->mall_id,
                "user_id" => $applyModel->user_id,
                "created_at" => time()
            ]);
        }

        $applyData = !empty($applyModel->json_apply_data) ? json_decode($applyModel->json_apply_data, true) : [];

        $settleDiscount    = isset($applyData['settle_discount']) ? (float)$applyData['settle_discount'] : 8;
        $isSpecialDiscount = isset($applyData['is_special_discount']) ? (int)$applyData['is_special_discount'] : 0;
        $specialRemark     = isset($applyData['settle_special_rate_remark']) ? (int)$applyData['settle_special_rate_remark'] : 0;

        $mch->review_status       = Mch::REVIEW_STATUS_CHECKED; //状态通过
        $mch->review_time         = time();
        $mch->realname            = $applyModel->realname;
        $mch->mobile              = $this->bind_mobile;
        $mch->updated_at          = time();
        $mch->transfer_rate       = (int)((10 - $settleDiscount) / 100) * 100;
        $mch->is_special          = $isSpecialDiscount;
        $mch->special_rate        = $mch->transfer_rate;
        $mch->special_rate_remark = $specialRemark;
        $mch->is_delete           = 0;

        if(!$mch->save()){
            throw new \Exception($this->responseErrorMsg($mch));
        }

        User::updateAll(["mch_id" => 0], ["mch_id" => $mch->id]);

        $user->mch_id = $mch->id;
        if(!$user->save()){
            throw new \Exception($this->responseErrorMsg($user));
        }

        return $mch;
    }

    /**
     * 保存门店信息
     * @param MchApply $applyModel
     * @param Mch $mch
     * @return Store|array|\yii\db\ActiveRecord|null
     * @throws \Exception
     */
    private function saveStore(MchApply $applyModel, Mch $mch){
        $applyData = !empty($applyModel->json_apply_data) ? json_decode($applyModel->json_apply_data, true) : [];

        $store = Store::find()->where([
            "mall_id" => $applyModel->mall_id,
            "mch_id"  => $mch->id
        ])->orderBy("is_default DESC")->one();
        if(!$store){
            $store = new Store([
                "mall_id"    => $applyModel->mall_id,
                "mch_id"     => $mch->id,
                "created_at" => time()
            ]);
        }

        $store->name        = $applyData['store_name'];
        $store->mobile      = (isset($applyData['bind_mobile']) && $applyData['bind_mobile']) ? $applyData['bind_mobile'] : $applyModel->mobile;
        $store->address     = $applyData['store_address'];
        $store->province_id = $applyData['store_province_id'];
        $store->city_id     = $applyData['store_city_id'];
        $store->district_id = $applyData['store_district_id'];
        $store->longitude   = $applyData['store_longitude'];
        $store->latitude    = $applyData['store_latitude'];
        $store->is_default  = 1;
        $store->updated_at  = time();
        $store->is_delete   = 0;

        if(!$store->save()){
            throw new \Exception($this->responseErrorMsg($store));
        }

        return $store;
    }

    /**
     * 保存易票联信息
     * @param MchApply $applyModel
     * @param Mch $mch
     * @throws \Exception
     */
    private function saveEfps(MchApply $applyModel, Mch $mch){
        $applyData = !empty($applyModel->json_apply_data) ? json_decode($applyModel->json_apply_data, true) : [];

        $efpsReview = EfpsMchReviewInfo::findOne(["mch_id" => $mch->id]);
        if(!$efpsReview){
            $efpsReview = new EfpsMchReviewInfo([
                "mch_id"     => $mch->id,
                "created_at" => time()
            ]);
        }

        $efpsReview->register_type = "separate_account";
        $efpsReview->status = 2; //审核成功
        $efpsReview->merchantName = $applyData['store_name'];
        $efpsReview->openAccount = 1;
        $efpsReview->paper_merchantType = 3;
        $efpsReview->paper_businessLicenseCode = $applyData['license_num'];
        $efpsReview->paper_businessLicenseName = $applyData['license_name'];
        $efpsReview->paper_businessLicensePhoto = $applyData['license_pic'];
        $efpsReview->paper_shortName = $applyData['store_name'];
        $efpsReview->paper_isCc = 1; //3证合一
        $efpsReview->paper_lawyerName = $applyData['cor_num'];
        $efpsReview->paper_lawyerCertType = 0;
        $efpsReview->paper_lawyerCertNo = $applyData['cor_num'];
        $efpsReview->paper_lawyerCertPhotoFront = $applyData['cor_pic1'];
        $efpsReview->paper_lawyerCertPhotoBack = $applyData['cor_pic2'];
        $efpsReview->paper_certificateName = $applyData['cor_num'];
        $efpsReview->paper_settleAccountType = 2;
        $efpsReview->paper_settleAccountNo = $applyData['settle_num']; //银行卡号
        $efpsReview->paper_settleAccount = $applyData['settle_realname']; //开户人
        $efpsReview->paper_settleTarget = 2; //手动提现
        $efpsReview->paper_openBank = $applyData['settle_bank']; //开户银行
        $efpsReview->paper_businessCode = "WITHDRAW_TO_SETTMENT_DEBIT";
        $efpsReview->updated_at = time();
        $efpsReview->is_delete = 0;

        if(!$efpsReview->save()){
            throw new \Exception($this->responseErrorMsg($efpsReview));
        }

    }
}