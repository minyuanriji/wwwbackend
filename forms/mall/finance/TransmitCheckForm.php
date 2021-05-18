<?php
namespace app\forms\mall\finance;


use app\core\ApiCode;
use app\forms\efps\EfpsCashTransfer;
use app\forms\efps\EfpsTransfer;
use app\models\BaseModel;
use app\models\Cash;

class TransmitCheckForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function check(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $res['msg']  = '打款成功';
        $res['code'] = ApiCode::CODE_SUCCESS;

        try {
            $cash = Cash::findOne(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0, 'id' => $this->id]);
            if (!$cash) {
                throw new \Exception("提现记录不存在");
            }

            if($cash->is_transmitting != 1){
                throw new \Exception("提现未提交");
            }

            if($cash->type == "bank"){
                $res = EfpsTransfer::query($cash->order_no);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    $cash->status = 1;
                    $res['code'] = ApiCode::CODE_FAIL;
                    $res['msg']  = $res['msg'];
                }else{
                    $cash->is_transmitting = 0;
                }
            }
        }catch (\Exception $e){
            $res['code'] = ApiCode::CODE_FAIL;
            $res['msg']  = $e->getMessage();
        }

        $cash->save();

        return $res;
    }
}