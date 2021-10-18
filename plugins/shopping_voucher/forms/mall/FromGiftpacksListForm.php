<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;

class FromGiftpacksListForm extends BaseModel {

    public function rules(){
        return [
            [[], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {


            $commonData = ["is_open" => 0, "give_value"  => "", "start_at" => ""];
            $commonData['recommender'] = [
                ['type' => 'branch_office', 'give_value' => 0],
                ['type' => 'partner', 'give_value' => 0],
                ['type' => 'store', 'give_value' => 0],
            ];
            $fromGiftpacks = ShoppingVoucherFromGiftpacks::findOne(["pack_id" => 0]);
            if($fromGiftpacks){
                $commonData["is_open"]    = !$fromGiftpacks->is_delete ? 1 : 0;
                $commonData["give_value"] = $fromGiftpacks->give_value;
                $commonData["start_at"]   = date("Y-m-d", $fromGiftpacks->start_at);

                $recommander = @json_decode($fromGiftpacks->recommender, true);
                if(is_array($recommander)){
                    foreach($recommander as $item1){
                        foreach($commonData['recommender'] as &$item2){
                            if($item1['type'] == $item2['type']){
                                $item2['give_value'] = floatval($item1['give_value']);
                                break;
                            }
                        }
                    }
                }
            }

            $list = [];
            $pagination = null;

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