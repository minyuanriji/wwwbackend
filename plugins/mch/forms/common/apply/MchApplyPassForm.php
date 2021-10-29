<?php
namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\EfpsMchReviewInfo;
use app\models\Store;
use app\models\User;
use app\plugins\integral_card\models\ScoreFromStore;
use app\plugins\mch\forms\api\MchApplyOperationLogSaveForm;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchApply;
use app\plugins\mch\models\MchApplyOperationLog;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;

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
                throw new \Exception("申请记录不存在");
            }

            if ($applyModel->status != "verifying") {
                throw new \Exception("非审核中状态无法操作");
            }

            //绑定手机号唯一值检查
            $existsMobile = Mch::find()->andWhere([
                "AND",
                ["mobile" => $this->bind_mobile],
                "user_id <> '".$applyModel->user_id."'"
            ])->exists();
            if($existsMobile){
                throw new \Exception("手机“".$this->bind_mobile."”已被其它商户绑定");
            }

            $user = User::findOne($applyModel->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取到用户信息");
            }


            //生成主商户记录
            $mch = $this->saveMch($applyModel, $user);

            //保存门店数据
            $store = $this->saveStore($applyModel, $mch);

            //保存易票联信息
            $this->saveEfps($applyModel, $mch);

            $applyModel->status     = "passed";
            $applyModel->updated_at = time();
            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel));
            }

            if ($operation_terminal == MchApplyOperationLog::OPERATION_TERMINAL_BACKSTAGE) {
                $user_id = \Yii::$app->admin->id;
            } else {
                $user_id = \Yii::$app->user->id;
            }
            $operation_save = MchApplyOperationLogSaveForm::addOperationLog($applyModel->mall_id, $applyModel->id, $operation_terminal, $user_id, MchApplyOperationLog::OPERATION_PASSED);
            if ($operation_save['code'] != ApiCode::CODE_SUCCESS)
                throw new \Exception(isset($operation_save['msg']) ? $operation_save['msg'] : $operation_save['message']);

            //设置二维码支付完成操作处理
            static::setCheckoutOrderPaidAction($mch, $store);

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
     * 设置二维码支付后操作
     * @param Mch $mch
     * @param Store $store
     * @throws \Exception
     */
    public static function setCheckoutOrderPaidAction(Mch $mch, Store $store){

        //计算赠送比例
        $value = (100 - $mch->transfer_rate)/10;
        if($value <= 8){ //8折以下都是100%
            $giveValue = 100;
        }elseif($value > 8 && $value <= 8.5){ //8折-8.5折，70%
            $x = (1 - 0.7)/(8.5-8) * ($value - 8);
            $giveValue = intval((1 - $x) * 100);
        }elseif($value > 8.5 && $value <= 9){ //8.5-9折，50%
            $x = (0.7 - 0.5)/(9-8.5) * ($value - 8.5);
            $giveValue = intval((0.7 - $x) * 100);
        }elseif($value > 9 && $value <= 9.5){ //9折-9.5折，30%
            $x = (0.5 - 0.3)/(9.5-9) * ($value - 9);
            $giveValue = intval((0.5 - $x) * 100);
        }else{
            $giveValue = 0;
        }

        //设置购物券赠送
        if($giveValue > 0){
            $model = ShoppingVoucherFromStore::findOne([
                "mall_id"  => $mch->mall_id,
                "store_id" => $store->id
            ]);
            if(!$model){
                $model = new ShoppingVoucherFromStore([
                    "mall_id"    => $mch->mall_id,
                    "mch_id"     => $mch->id,
                    "store_id"   => $store->id,
                    "created_at" => time()
                ]);
            }
            $model->give_type  = 1;
            $model->give_value = max(0, min(100, $giveValue));
            $model->updated_at = time();
            $model->is_delete  = 0;
            $model->name       = $store->name;
            $model->cover_url  = $store->cover_url;
            $model->start_at   = time();
            if(!$model->save()){
                throw new \Exception(json_encode($model->getErrors()));
            }
        }

        //设置积分赠送
        if($giveValue > 0){
            $fromStore = ScoreFromStore::findOne([
                "store_id" => $store->id
            ]);
            if(!$fromStore){
                $fromStore = new ScoreFromStore([
                    "mall_id"    => $mch->mall_id,
                    "created_at" => time()
                ]);
            }

            $scoreSetting['is_permanent'] = 1;
            $scoreSetting['integral_num'] = 0;
            $scoreSetting['expire']       = -1;
            $scoreSetting['period_unit']  = "month";
            $scoreSetting['period']       = 1;

            $fromStore->mch_id        = $mch->id;
            $fromStore->store_id      = $store->id;
            $fromStore->updated_at    = time();
            $fromStore->name          = $store->name;
            $fromStore->cover_url     = $store->cover_url;
            $fromStore->start_at      = time();
            $fromStore->enable_score  = 1;
            $fromStore->score_setting = json_encode($scoreSetting) ;
            $fromStore->rate          = max(0, min(100, $giveValue));

            if(!$fromStore->save()){
                throw new \Exception(json_encode($fromStore->getErrors()));
            }
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

        $mch->mch_common_cat_id   = $applyData['store_mch_common_cat_id'];
        $mch->review_status       = Mch::REVIEW_STATUS_CHECKED; //状态通过
        $mch->review_time         = date('Y-m-d H:i:s', time());
        $mch->realname            = $applyModel->realname;
        $mch->mobile              = $this->bind_mobile;
        $mch->updated_at          = time();
        $mch->transfer_rate       = strval((10 - $settleDiscount) * 10);
        $mch->integral_fee_rate   = $mch->transfer_rate <= 10 ? 10 : 0;
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
        $store->description = "";
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
