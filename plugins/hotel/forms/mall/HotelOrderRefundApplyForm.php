<?php
namespace app\plugins\hotel\forms\mall;


use app\core\ApiCode;
use app\plugins\hotel\forms\common\HotelOrderRefundActionForm;
use app\plugins\hotel\models\HotelRefundApplyOrder;

class HotelOrderRefundApplyForm extends HotelOrderListForm{

    public $id;
    public $act;
    public $remark;

    public function rules(){
        return [
            [['id', 'act'], 'required'],
            [['remark'], 'safe']
        ];
    }

    public function apply(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }


        try {

            $applyOrder = HotelRefundApplyOrder::findOne($this->id);
            if(!$applyOrder){
                throw new \Exception("售后申请记录不存在");
            }

            $actionForm = new HotelOrderRefundActionForm([
                "order_id" => $applyOrder->order_id,
                "mall_id"  => $applyOrder->mall_id,
                "remark"   => $this->remark
            ]);

            if($this->act == "confirm"){ //确认
                $actionForm->action = "confirm";
            }elseif($this->act == "refuse"){ //拒绝
                $actionForm->action = "refuse";
            }elseif($this->act == "paid"){ //打款
                $actionForm->action = "paid";
            }

            $res = $actionForm->refund();
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