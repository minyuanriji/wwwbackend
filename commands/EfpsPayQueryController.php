<?php
namespace app\commands;


use app\component\jobs\EfpsPayQueryJob;

class EfpsPayQueryController extends BaseCommandController{

    public function actionMaintantJob(){

        $this->mutiKill();

        echo date("Y-m-d H:i:s") . " 支付结果状态守护程序启动...完成\n";
        while (true) {

            sleep(3);

            try {
                $job = new EfpsPayQueryJob();
                $job->execute(null);
            }catch (\Exception $e) {
                \Yii::error("查询出现异常 File=".$e->getFile().";Line:".$e->getLine().";message:".$e->getMessage());
            }
        }

    }
}