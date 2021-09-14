<?php
/*
 * @link:http://www.@copyright: Copyright (c) @Author: Mr.Lin
 * @Email: 746027209@qq.com
 * @Date: 2021-07-06 14:13
 */

namespace app\commands;

use app\component\efps\Efps;
use app\forms\common\UserIntegralModifyForm;
use app\forms\mall\finance\IntegralModifiedForm;
use app\models\EfpsPaymentOrder;
use app\models\IncomeLog;
use app\models\IntegralLog;
use app\models\Order;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\models\User;
use app\plugins\commission\models\CommissionGoodsPriceLog;

class DebugController extends BaseCommandController{

    public function actionIndex(){



       /* $sql = 'select * from jxmall_plugin_commission_goods_price_log where order_id IN(
                    select o.id from jxmall_order o 
                    INNER JOIN jxmall_payment_order po on po.order_no=o.order_no
                    where po.payment_order_union_id IN(
                    4384,4388,4389,4395,4396,4398,4399,4457,4468,4480,4492,4502,4509,4519,4526,4562,4620,4626,4673,4679,4698,4701,4720,4721,4722,4723,4737,4742,4752,4793,4795,4842,4843,4849,4866,4872,4876,4898,4948,4958,5028,5031,5043,5055,5069,5072,5074,5075,5107,5121,5171,5183,5192,5201,5266,5269,5278,5295,5296,5301,5320,5325,5329,5344,5347,5354,5363,5422,5437,5447,5449,5478,5498,5501,5520,5521,5545,5548,5571,5572,5649,5661,5671,5684,5688,5690,5693,5698,5715,5728,5758,5762,5784,5802,5815,5848,5889,5895,5898,5919,5938,5940,5941,5962,5967,6000,6003,6035,6053,6060,6077,6085,6109,6121,6123,6124,6130,6153,6193,6210,6217,6221,6224,6225,6229,6235,6240,6248,6271,6278,6280,6301,6321,6331,6338,6341,6362,6399,6400,6410,6413,6421,6435,6442,6457,6473,6474,6517,6549,6566,6607,6628,6641,6669,6676,6689,6706,6753,6764,6778,6790,6800,6818,6826,6832,6835,6859,6876,6887,6897,6909,6930,6945,6958,6965,7002,7005,7007,7033,7060,7067,7164,7165,7205,7217,7218,7224,7236,7237,7246,7253,7255,7283,7284,7287,7307,7356,7367,7370,7381,7395,7407,7417,7422,7426,7428,7432,7440,7444,7446,7447,7455,7456,7486,7494,7499,7506,7510,7511,7515,7531,7533,7539,7551,7577,7595,7674,7686,7705,7712,7713,7714,7715,7716,7723,7759,7761,7769,7780,7783,7790,7820,7836,7838,7839,7847,7861,7863,7901,7902,7934,7946,7955,7963,7969,7970,7996,8029,8030,8036,8040,8117,8126,8138,8145,8158,8195,8206,8208,8222,8224,8240,8244,8252,8275,8300,8303,8304,8305,8307,8312,8314,8317,8356,8357,8358,8386,8409,8413,8417,8428,8445,8486,8509,8510,8511,8515,8516,8546,8547,8548,8591,8597,8612,8637,8641,8656,8672,8712,8731,8743,8767,8770,8778,8782,8796,8806,8851,8853,8870,8876,8877,8885,8887,8916,8917,8918,8919,8928,8929,8930,8940,8948,8949,8955,9006,9033,9045,9055,9096,9103,9109,9127,9134,9151,9164,9216,9218,9244,9361,9366,9400,9402,9417,9469,9489,9497,9503,9519,9529,9544,9552,9569,9591,9596,9605,9626,9627,9638,9666,9676,9677,9678,9679,9680,9690,9712,9739,9740,9761,9797,9798,9814,9821,9826,9842,9843,9862,9895,9913,9917,9927,9938,9959,9969,9993,10013,10038,10047,10065,10066,10067,10077,10112,10151,10184,10207,10266,10268,10301,10302,10311,10318,10323,10340,10343,10367,10381,10436,10466,10526,10538,10542,10563,10578,10590,10602,10620,10634,10673,10714,10723,10759,10762,10777,10786,10818,10821,10823,10840,10846,10847,10851,10860,10871,10891,10903,10913,10926,10973,10990,10992,10995,11006,11011,11024,11029,11032,11040,11042,11043,11048,11052,11055,11068,11076,11081,11083,11089,11106,11121,11140,11141,11142,11157,11158,11179,11185,11188,11194,11229,11233,11303,11304,11324,11358,11359,11386,11387,11397,11408,11409,11413,11445,11446,11448,11455,11465,11473,11474,11477,11504,11538,11552,11559,11568,11587,11588,11595,11596,11598,11603,11628,11637,11658,11667,11678,11677,11683,11704,11715,11717,11727,11738,11748,11758,11760,11764,11768,11802,11820,11827,11833,11836,11847,11849,11850,11851,11853,11854,11859,11867,11873,11887,11889,11891,11892,11914,11920,11923,11925,11943,12001,12006,12051,12112,12139,12142,12145,12146,12166,12176,12178,12208,12221,12246,12248,12259,12288,12299,12303,12310,12328,12329,12333,12340,12341,12386,12404,12405,12413,12414,12434,12437,12440,12473,12474,12480,12481,12498,12500,12503,12523,12562,12582,12592,12603,12645,12661,12677,12680,12693,12705,12720,12740,12806,12838,12880,12881,12882,12891,12894,12895,12913,12925,13009,13014,13052,13084,13094,13102,13117,13168,13207,13221,13241,13243,13250,13252,13253,13268,13272,13281,13284,13292,13314,13342,13345,13355,13363,13388,13389,13437,13439,13448,13450,13464,13475,13477,13482,13493,13495,13567,13592,13606,13614,13631,13642,13646,13651,13660,13666,13673,13675,13688,13691,13692,13693,13697,13747,13753,13764,13768,13771,13794,13795,13811,13818,13829,13832,13836,13840,13849,13851,13852,13854,13863,13865,13886,13889,13891,13892,13893,13902,13917,13920,13922,13927,13936,13951,13961,13977,13978,13981,13989,13995,14007,14030,14033,14065,14067,14071,14072,14088,14096,14136,14140,14141,14145,14157,14162,14201,14203,14272,14274,14293,14294,14332,14355,14359,14395,14449,14450,14463,14464,14467,14468,14471,14487,14493,14501,14523,14524,14572,14580,14584,14597,14600,14621,14627,14633,14684,14704,14705,14712,14716,14722,14726,14751,14810,14815,14820,14841,14844,14846,14857,14858,14859,14862,14886,14892,14895,14941,14957,14967,14980,15003,15007,15113,15131,15143,15149,15150,15151,15157
                    ) and po.pay_type=1
                ) and `status` >= 0
        ';
        $alls = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach($alls as $priceLog){
            echo "ID:" . $priceLog['order_id'] . "\n";
            $trans = \Yii::$app->db->beginTransaction();
            try {

                //更新分佣记录为已取消
                CommissionGoodsPriceLog::updateAll([
                    "status" => -1
                ], ["id" => $priceLog['id']]);

                //新增一条收入支出记录
                $user = User::findOne($priceLog['user_id']);
                if($user){
                    $totalIncome = $user->income + $user->income_frozen;
                    $incomeLog = new IncomeLog([
                        'mall_id'     => $priceLog['mall_id'],
                        'user_id'     => $priceLog['user_id'],
                        'type'        => 2,
                        'money'       => $totalIncome,
                        'income'      => $priceLog['price'],
                        'desc'        => "系统回滚，取消订单[ID:".$priceLog['order_id']."]，扣除佣金",
                        'flag'        => $priceLog['status'], //冻结
                        'source_id'   => $priceLog['id'],
                        'source_type' => 'goods',
                        'created_at'  => time(),
                        'updated_at'  => time()
                    ]);
                    if(!$incomeLog->save()){
                        throw new \Exception(json_encode($incomeLog->getErrors()));
                    }

                    //更新用户收入信息
                    if($priceLog['status'] == 0){
                        User::updateAllCounters([
                            "total_income"  => -1 * abs($priceLog['price']),
                            "income_frozen" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }else{
                        User::updateAllCounters([
                            "total_income"  => -1 * abs($priceLog['price']),
                            "income" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }
                }
                $trans->commit();
            }catch (\Exception $e){
                $trans->rollBack();
            }
        }
        exit;*/


        /*$sql = "select * from jxmall_efps_payment_order where update_at>'1630897200' AND update_at<'1630899300' and is_pay=1";
        $rows = \Yii::$app->db->createCommand($sql)->queryAll();
        $errorIds = [];
        foreach($rows as $key => $row){
            $res = \Yii::$app->efps->payQuery([
                "customerCode" => \Yii::$app->efps->getCustomerCode(),
                "outTradeNo"   => $row['outTradeNo']
            ]);
            echo $key . "/" . count($rows) . " ";
            if($res['code'] != Efps::CODE_SUCCESS || $res['data']['payState'] != "00"){
                echo "error:" . $row['payment_order_union_id'] . "\n";
                $errorIds[] = $row['payment_order_union_id'];
            }
        }
        file_put_contents(__DIR__ . "/debug", implode(",", $errorIds));
        exit;*/

        /*$sql = "
            select id,user_id,integral,type,integral,current_integral,`desc`
            from jxmall_integral_log where created_at>'1630897200' and created_at <'1630899300' and type=1 and is_manual=0;
        ";
        $rows = \Yii::$app->db->createCommand($sql)->queryAll();
        foreach($rows as $row){
            $modify = new UserIntegralModifyForm([
                "type" => 2,
                "integral" => $row['integral'],
                "desc" => "系统回调:{$row['id']}",
                "source_id" => $row['id'] + rand(600000, 999999),
                "source_type" => "admin",
                "is_manual" => 0
            ]);
            $modify->modify(User::findOne($row['user_id']));
        }
        exit;


        $query = CommissionGoodsPriceLog::find()->alias("l");
        $query->innerJoin(["o" => Order::tableName()], "o.id=l.order_id");
        $query->andWhere([
            "AND",
            "l.created_at>'1630897200'",
            ["o.is_pay" => 0],
            "(l.status>0 OR l.status=0)"
        ]);
        $alls = $query->select(["l.*"])->asArray()->all();
        foreach($alls as $priceLog){
            echo "ID:" . $priceLog['order_id'] . "\n";
            $trans = \Yii::$app->db->beginTransaction();
            try {

                //更新分佣记录为已取消
                CommissionGoodsPriceLog::updateAll([
                    "status" => -1
                ], ["id" => $priceLog['id']]);

                //新增一条收入支出记录
                $user = User::findOne($priceLog['user_id']);
                if($user){
                    $totalIncome = $user->income + $user->income_frozen;
                    $incomeLog = new IncomeLog([
                        'mall_id'     => $priceLog['mall_id'],
                        'user_id'     => $priceLog['user_id'],
                        'type'        => 2,
                        'money'       => $totalIncome,
                        'income'      => $priceLog['price'],
                        'desc'        => "系统回滚，取消订单[ID:".$priceLog['order_id']."]，扣除佣金",
                        'flag'        => $priceLog['status'], //冻结
                        'source_id'   => $priceLog['id'],
                        'source_type' => 'goods',
                        'created_at'  => time(),
                        'updated_at'  => time()
                    ]);
                    if(!$incomeLog->save()){
                        throw new \Exception(json_encode($incomeLog->getErrors()));
                    }

                    //更新用户收入信息
                    if($priceLog['status'] == 0){
                        User::updateAllCounters([
                            "total_income"  => -1 * abs($priceLog['price']),
                            "income_frozen" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }else{
                        User::updateAllCounters([
                            "total_income"  => -1 * abs($priceLog['price']),
                            "income" => -1 * abs($priceLog['price'])
                        ], ["id" => $priceLog['user_id']]);
                    }
                }
                $trans->commit();
            }catch (\Exception $e){
                $trans->rollBack();
            }
        }
        exit;

        $query = Order::find()->alias("o");
        $query->innerJoin(["po" => PaymentOrder::tableName()], "po.order_no=o.order_no");
        $query->innerJoin(["pou" => PaymentOrderUnion::tableName()], "pou.id=po.payment_order_union_id");
        $query->innerJoin(["epo" => EfpsPaymentOrder::tableName()], "epo.payment_order_union_id=pou.id");

        $orders = $query->andWhere([
            "AND",
            ["o.status" => 1],
            ["o.is_pay" => 1],
            "epo.update_at>'1630897200'",
            "(o.total_pay_price+o.express_original_price) > 0"
        ])->asArray()->select(["o.order_no", "epo.outTradeNo"])->all();
        foreach($orders as $key => $order){
            $res = \Yii::$app->efps->payQuery([
                "customerCode" => \Yii::$app->efps->getCustomerCode(),
                "outTradeNo"   => $order['outTradeNo']
            ]);
            echo "count:" . count($orders) . " " . $key . " ";
            if($res['code'] != Efps::CODE_SUCCESS || $res['data']['payState'] != "00"){
                Order::updateAll([
                    "is_pay" => 0,
                    "status" => 0,
                    "remark" => "系统回滚"
                ], ["order_no" => $order['order_no']]);
                echo $order['order_no'] . " no paid\n";
            }else{
                echo json_encode($res) . "\n";
            }
        }*/

    }
}