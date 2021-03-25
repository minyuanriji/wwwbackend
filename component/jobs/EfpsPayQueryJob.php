<?php
namespace app\component\jobs;


use app\component\efps\Efps;
use app\models\PaymentEfpsOrder;
use yii\base\Component;
use yii\queue\JobInterface;

class EfpsPayQueryJob extends Component implements JobInterface{

    public $outTradeNo;

    public function execute($queue){
        $t = \Yii::$app->db->beginTransaction();
        try {
            \Yii::warning('----EfpsPayQueryJob start----');
            if(empty($this->outTradeNo)){
                $efpsOrder = PaymentEfpsOrder::find()->where([
                    "is_pay" => 0
                ])->orderBy("update_at ASC")->one();
                if($efpsOrder){
                    $this->outTradeNo = $efpsOrder->outTradeNo;
                    $efpsOrder->update_at = time();
                    $efpsOrder->save();
                }
            }
            $res = \Yii::$app->efps->payQuery([
                "customerCode" => \Yii::$app->efps->getCustomerCode(),
                "outTradeNo"   => $this->outTradeNo
            ]);
            if($res['code'] == Efps::CODE_SUCCESS && $res['data']['payState'] == "00"){
                echo '支付成功！\n';
            }
            \Yii::warning('----EfpsPayQueryJob end----');
            $t->commit();
        }catch (\Exception $e) {
            $t->rollBack();
            \Yii::error("查询出现异常 File=".$e->getFile().";Line:".$e->getLine().";message:".$e->getMessage());
        }
    }

}