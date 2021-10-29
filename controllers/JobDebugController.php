<?php
namespace app\controllers;



use app\forms\common\WebSocketRequestForm;
use app\mch\handlers\CheckoutOrderPaidHandler;
use app\models\EfpsTransferOrder;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\jing36\PlateForm;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromAddcredit;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;

class JobDebugController extends BaseController {

    public function actionIndex(){
        CheckoutOrderPaidHandler::voiceNotify("bNQMOmAywCEffAiOds9tABECSXPasRLX", "补商汇到账100元");
        exit;
        /*$query = AddcreditOrder::find()->alias("ao");
        $query->leftJoin(["apf" => AddcreditPlateforms::tableName()], "ao.plateform_id=apf.id");
        $query->innerJoin(["svfa" => ShoppingVoucherFromAddcredit::tableName()], "(svfa.sdk_key=apf.sdk_dir) AND svfa.is_delete=0");
        $query->leftJoin(["svs" => ShoppingVoucherSendLog::tableName()], "svs.source_id=ao.id AND svs.source_type='from_addcredit_order'");
        $query->andWhere([
            "AND",
            "ao.pay_price > 0",
            "svs.id IS NULL",
            ["ao.pay_status" => 'paid']
        ]);
        $query->orderBy("ao.updated_at ASC");

        $selects = ["ao.id", "ao.mall_id", "ao.mobile", "ao.user_id", "ao.pay_price", "svfa.param_data_json", 'ao.product_id'];

        echo $query->select($selects)->asArray()->limit(10)->createCommand()->getRawSql();
        exit;*/

        /*$transferOrder = EfpsTransferOrder::findOne(2065);
        echo ceil(floatval($transferOrder->amount) * 100);
        exit;*/

        /*$plateModel = AddcreditPlateforms::findOne(2);
        $orderModel = AddcreditOrder::findOne(2);

        $plat = new PlateForm();
        $result = $plat->query2($orderModel, $plateModel);
        PRINT_R($result);
        EXIT;*/

/*        $distribution = new Distribution("1265913", "twWQgEYSoiKU");
        $distribution->requestWithToken(new GetGoodsListForUserChoosed([
            "pageNo" => 1,
            "pageSize" => 100
        ]), "d414dce3-67d6-44be-8a0d-d3f773d73e2f2");*/

        /*$auth = new WebOauth2("1265913",  "twWQgEYSoiKU", "http://local.mingyuanriji.cn/web/index.php?r=job-debug/index");
        $auth->auth();

        if($auth->error){
            echo $auth->error;
        }else{
            $auth->tokenInfo();
            exit;
        }*/
    }

}
