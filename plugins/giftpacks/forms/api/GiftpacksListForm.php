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

            $list = Giftpacks::find()->where(["is_delete" => 0])
                        ->orderBy("updated_at DESC")
                        ->page($pagination, 10, max(1, (int)$this->page))
                        ->asArray()->all();;
            if($list){
                foreach($list as &$item){
                    $item['max_stock'] = (int)$item['max_stock'];
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