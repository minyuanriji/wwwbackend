<?php

namespace app\plugins\taolijin\helpers;

use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinAli;
use lin010\taolijin\Ali;

class AliOrderHelper{

    /**
     * 获取联盟订单
     * @param TaolijinAli $aliModel
     * @return array
     */
    public static function get(TaolijinAli $aliModel, $page){

        $acc = AliAccForm::getByModel($aliModel);

        $ali = new Ali($acc->app_key, $acc->secret_key);
        $res = $ali->order->get([
            "page_no"        => (string)$page,
            "page_size"      => "12",
            "position_index" => $acc->adzone_id,
            "start_time"     => "2021-11-30 11:00",
            "end_time"       => "2021-11-30 12:00"
        ]);
        print_r($res);
        exit;
        if(!empty($res->code)){
            throw new \Exception($res->msg);
        }


    }

}