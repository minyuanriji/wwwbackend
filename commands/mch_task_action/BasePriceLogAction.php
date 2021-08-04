<?php

namespace app\commands\mch_task_action;


use app\core\ApiCode;
use app\mch\forms\mch\MchAccountModifyForm;
use app\models\Mall;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchPriceLog;
use yii\base\Action;

abstract class BasePriceLogAction extends Action{

    public function run(){
        while(true) {
            if(!$this->doSuccess()){
                if(!$this->doCanceled()){
                    sleep(5);
                }
            }
        }
    }

    /**
     * 获取可结算记录
     * @return MchPriceLog
     */
    abstract public function getPriceLog();

    /**
     * 设置结算成功
     * @return boolean
     */
    protected function doSuccess(){

        $priceLog = $this->getPriceLog();
        if(!$priceLog){
            return false;
        }

        $priceLog->updated_at = time();
        $priceLog->save();

        $otherData = (array)@json_decode($priceLog->other_json_data);

        \Yii::$app->mall = Mall::findOne($priceLog->mall_id);

        $t = \Yii::$app->getDb()->beginTransaction();
        try {

            //修改商家帐户
            $mch = Mch::findOne($priceLog->mch_id);
            if($mch && !$mch->is_delete){
                $res = MchAccountModifyForm::modify($mch, $priceLog->price, $priceLog->content, true);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    $otherData['remark'] = $res['msg'];
                }
            }else{
                $otherData['remark'] = "无法获取商家[ID:".$priceLog->mch_id."]信息";
            }

            //设置结算记录状态为已成功
            MchPriceLog::updateAll([
                "updated_at"      => time(),
                "status"          => "success",
                "other_json_data" => json_encode($otherData)
            ], ["id" => $priceLog->id]);

            $t->commit();

            $this->controller->commandOut("商家订单结算记录[ID:".$priceLog->id."]处理成功");

        }catch (\Exception $e){
            $t->rollBack();
            $this->controller->commandOut($e->getMessage());
        }
    }

    /**
     * 设置结算失败
     * @return boolean
     */
    protected function doCanceled(){}
}