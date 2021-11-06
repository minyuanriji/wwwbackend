<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\oil\models\OilPlateforms;
use app\plugins\oil\models\OilProduct;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;

class FromOilListForm extends BaseModel {

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $commonData = ["is_open" => 0,
                "first_give_value"  => "", "first_give_type"  => "1",
                "second_give_value"  => "", "second_give_type"  => "1",
                "start_at" => ""
            ];

            $fromOil = ShoppingVoucherFromOil::findOne(["plat_id" => 0, "product_id" => 0]);
            if($fromOil){
                $commonData["is_open"]           = !$fromOil->is_delete ? 1 : 0;
                $commonData["first_give_type"]   = (string)$fromOil->first_give_type;
                $commonData["first_give_value"]  = $fromOil->first_give_value;
                $commonData["second_give_type"]  = (string)$fromOil->second_give_type;
                $commonData["second_give_value"] = $fromOil->second_give_value;
                $commonData["start_at"]          = date("Y-m-d", $fromOil->start_at);
            }

            $query = ShoppingVoucherFromOil::find()->alias("svfo")->where(["svfo.is_delete" => 0]);
            $query->innerJoin(["op" => OilPlateforms::tableName()], "op.id=svfo.plat_id");
            $query->leftJoin(["opp" => OilProduct::tableName()], "opp.id=svfo.product_id");

            $selects = ["svfo.*", "op.name as plat_name", "opp.name as product_name", "opp.price as product_price"];
            $query->orderBy("svfo.id DESC");

            $list = $query->select($selects)->page($pagination, 10, $this->page)->asArray()->all();
            if($list) {
                foreach ($list as &$item) {
                    $item['start_at'] = date("Y-m-d", $item['start_at'] ? $item['start_at'] : time());
                }
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'commonData' => $commonData,
                'pagination' => $pagination
            ]);

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}