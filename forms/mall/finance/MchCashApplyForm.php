<?php
namespace app\forms\mall\finance;


use app\component\efps\Efps;
use app\core\ApiCode;
use app\forms\efps\EfpsMchCashTransfer;
use app\mch\forms\mch\MchAccountModifyForm;
use app\models\BaseModel;
use app\plugins\mch\models\MchCash;

class MchCashApplyForm  extends BaseModel{

    public $id;
    public $act;
    public $content;

    public function rules(){
        return [
            [['id', 'act'], 'required'],
            [['id'], 'integer'],
            [['content'], 'safe']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->getDb()->beginTransaction();
        try {
            $mchCash = MchCash::findOne($this->id);
            if(!$mchCash || $mchCash->is_delete){
                throw new \Exception("提现记录不存在");
            }

            $mchCash->content = $this->content;

            if($this->act == "confirm"){ //确认
                if($mchCash->status != 0){
                    throw new \Exception("无法确认操作");
                }
                $mchCash->status = 1;
                $mchCash->updated_at = time();
                if(!$mchCash->save()){
                    throw new \Exception($this->responseErrorMsg($mchCash));
                }
            }elseif($this->act == "refuse") { //拒绝
                if ($mchCash->status != 2) {
                    throw new \Exception("无法拒绝操作");
                }
                $mchCash->status = 2;
                if (!$mchCash->save()) {
                    throw new \Exception(json_encode($mchCash->getErrors()));
                }
            }elseif($this->act == "return"){ //退还账户余额
                if ($mchCash->status != 2 || $mchCash->transfer_status != 0) {
                    throw new \Exception("无法退还账户余额操作");
                }

                //再次查询易票联是否打款成功
                $res = \Yii::$app->efps->withdrawalToCardQuery([
                    "customerCode" => \Yii::$app->efps->getCustomerCode(),
                    "outTradeNo"   => $mchCash->order_no
                ]);
                if($res['code'] == Efps::CODE_SUCCESS && $res['data']['payState'] == "00"){
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg'  => "已经成功打款，无需退还！"
                    ];
                }

                $mchCash->transfer_status = 2;
                if (!$mchCash->save()) {
                    throw new \Exception(json_encode($mchCash->getErrors()));
                }

                $form = new MchAccountModifyForm([
                    'mall_id' => $mchCash->mall_id,
                    'mch_id' => $mchCash->mch_id,
                    'type' => 1,
                    'money' => $mchCash->money,
                    'desc' => '提现失败，退还账户余额'
                ]);
                $res = $form->save();
                if ($res['code'] != ApiCode::CODE_SUCCESS) {
                    throw new \Exception($res['msg']);
                }
            }elseif($this->act == "paid"){

                if($mchCash->status != 1 || $mchCash->transfer_status != 0){
                    throw new \Exception("无法打款操作");
                }

                $res = EfpsMchCashTransfer::transfer($mchCash);

                if($res['code'] == ApiCode::CODE_SUCCESS){ //打款成功
                    $mchCash->status = 1;
                    $mchCash->transfer_status = 1;
                    if (!$mchCash->save()) {
                        throw new \Exception(json_encode($mchCash->getErrors()));
                    }
                }else{
                    throw new \Exception($res['msg']);
                }
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}