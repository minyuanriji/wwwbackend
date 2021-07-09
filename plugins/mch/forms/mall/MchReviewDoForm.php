<?php
namespace app\plugins\mch\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\forms\common\apply\MchApplyPassForm;
use app\plugins\mch\forms\common\apply\MchApplyRefuseForm;
use app\plugins\mch\models\MchApply;

class MchReviewDoForm extends BaseModel{

    public $id;
    public $act;
    public $remark;
    public $store_name;
    public $bind_mobile;
    public $store_mch_common_cat_id;
    public $store_address;
    public $settle_discount;

    public function rules(){
        return [
            [['id', 'act', 'store_name', 'bind_mobile', 'store_mch_common_cat_id', 'store_address'], 'required'],
            [['remark', 'settle_discount'], 'safe']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $applyModel = MchApply::findOne($this->id);
            if(!$applyModel){
                throw new \Exception("申请记录不存在");
            }

            $applyData = !empty($applyModel->json_apply_data) ? json_decode($applyModel->json_apply_data, true) : [];
            $applyData['bind_mobile'] = $this->bind_mobile;
            $applyData['store_mch_common_cat_id'] = $this->store_mch_common_cat_id;
            $applyData['store_name'] = $this->store_name;
            $applyData['store_address'] = $this->store_address;
            $applyData['settle_discount'] = $this->settle_discount;

            $applyModel->updated_at = time();
            $applyModel->json_apply_data = json_encode($applyData);
            if(!$applyModel->save()){
                throw new \Exception($this->responseErrorMsg($applyModel));
            }

            if($this->act == "passed"){ //通过
                $form = new MchApplyPassForm([
                    "bind_mobile" => $applyData['bind_mobile']
                ]);
            }else{ //拒绝
                $form = new MchApplyRefuseForm([
                    "remark" => $this->remark
                ]);
            }
            $form->id = $applyModel->id;
            $res = $form->save();

            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}