<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;

class FromGiftpacksListForm extends BaseModel {

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

            $commonData = ["is_open" => 0, "give_value"  => "", "start_at" => ""];
            $commonData['recommender'] = [
                ['type' => 'branch_office', 'give_type' => "1", 'give_value' => 0],
                ['type' => 'partner', 'give_type' => "1", 'give_value' => 0],
                ['type' => 'store', 'give_type' => "1", 'give_value' => 0],
                ['type' => 'user', 'give_type' => "1", 'give_value' => 0]
            ];
            $fromGiftpacks = ShoppingVoucherFromGiftpacks::findOne(["pack_id" => 0]);
            if($fromGiftpacks){
                $commonData["is_open"]    = !$fromGiftpacks->is_delete ? 1 : 0;
                $commonData["give_type"]  = (string)$fromGiftpacks->give_type;
                $commonData["give_value"] = $fromGiftpacks->give_value;
                $commonData["start_at"]   = date("Y-m-d", $fromGiftpacks->start_at);

                $recommander = @json_decode($fromGiftpacks->recommender, true);
                if(is_array($recommander)){
                    foreach($recommander as $item1){
                        foreach($commonData['recommender'] as &$item2){
                            if($item1['type'] == $item2['type']){
                                $item2['give_type'] = isset($item1['give_type']) ? (string)$item1['give_type'] : $item2['give_type'];
                                $item2['give_value'] = floatval($item1['give_value']);
                                break;
                            }
                        }
                    }
                }
            }

            $query = ShoppingVoucherFromGiftpacks::find()->alias("svfgp")->where(["svfgp.is_delete" => 0]);
            $query->innerJoin(["gp" => Giftpacks::tableName()], "gp.id=svfgp.pack_id");

            $selects = ["svfgp.*", "gp.title", "gp.cover_pic"];
            $query->orderBy("svfgp.id DESC");

            $list = $query->select($selects)->page($pagination, 10, $this->page)->asArray()->all();
            if($list) {
                foreach ($list as &$item) {
                    $item['recommender'] = @json_decode($item['recommender'], true);
                    $item['start_at']    = date("Y-m-d", $item['start_at'] ? $item['start_at'] : time());
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