<?php

namespace app\plugins\giftpacks\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;

class GiftpacksListForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Giftpacks::find()->alias("gf")->where(["gf.is_delete" => 0])
                        ->orderBy("gf.updated_at DESC");

            $selects = ["gf.*"];
            $selects[] = "(IFNULL((select count(*) from {{%plugin_giftpacks_order}} where pay_status='paid' AND is_delete=0 AND pack_id=gf.id), 0) + IFNULL((select sum(user_num) from {{%plugin_giftpacks_group}} where status='sharing' AND pack_id=gf.id), 0)) as sold_num";

            $list = $query->select($selects)->page($pagination, 10, max(1, (int)$this->page))
                        ->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $item['max_stock'] = (int)$item['max_stock'];
                    $item['item_num']  = (int)GiftpacksDetailForm::availableItemsQueryByPackId($item['id'])->count();
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}