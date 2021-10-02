<?php
namespace app\plugins\mch\forms\common\apply;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchApply;

class MchApplyLicenseForm extends BaseModel{

    public $user_id;
    public $license_num;
    public $license_name;
    public $license_pic;
    public $cor_realname;
    public $cor_num;
    public $cor_pic1;
    public $cor_pic2;
    public $settle_num;
    public $settle_realname;
    public $settle_bank;
    public $settle_discount;

    public function rules(){
        return [
            [['user_id', 'license_name', 'license_pic'], 'required'],//'license_num'
            [['user_id'], 'integer'],
            [['settle_num', 'settle_realname', 'settle_bank', 'settle_discount', 'cor_realname', 'cor_num', 'cor_pic1', 'cor_pic2'], 'safe']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $applyModel = MchApply::findOne([
                "user_id" => $this->user_id
            ]);
            if(!$applyModel){
                throw new \Exception("无法获取到申请信息");
            }

            if($applyModel->status != "applying"){
                throw new \Exception("申请操作还未结束，请耐心等待");
            }

            if(!empty($this->settle_discount) && $this->settle_discount > 9){
                throw new \Exception("店铺折扣不能大于9折");
            }

            $applyData = @json_decode($applyModel->json_apply_data, true);
            $applyData['license_num']        = '';//$this->license_num   7/31 暂时取消
            $applyData['license_name']       = $this->license_name;
            $applyData['license_pic']        = $this->license_pic;
            $applyData['cor_num']            = $this->cor_num;
            $applyData['cor_pic1']           = $this->cor_pic1;
            $applyData['cor_pic2']           = $this->cor_pic2;
            $applyData['cor_realname']       = $this->cor_realname;
            $applyData['settle_bank']        = $this->settle_bank;
            $applyData['settle_num']         = $this->settle_num;
            $applyData['settle_realname']    = $this->settle_realname;
            $applyData['settle_discount']    = $this->settle_discount;

            $applyModel->updated_at = time();
            $applyModel->json_apply_data = json_encode($applyData);
            $applyModel->status = "verifying";

            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}