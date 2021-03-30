<?php
namespace app\controllers;


use app\component\jobs\CheckoutOrderDistributionIncomeJob;
use app\component\jobs\EfpsPayQueryJob;
use app\component\jobs\EfpsTransferJob;
use app\component\jobs\OrderDistributionIncomeJob;
use yii\web\Controller;

class JobDebugController extends Controller{

    public function actionExecute(){

        (new EfpsPayQueryJob())->execute(null);
        (new OrderDistributionIncomeJob())->execute(null);
        (new CheckoutOrderDistributionIncomeJob())->execute(null);
        (new EfpsTransferJob())->execute(null);

    }
}