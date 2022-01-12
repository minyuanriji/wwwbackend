<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\components\SmartShop;

class MerchantGetSmartshopForm extends BaseModel{

    public $page;
    public $limit = 10;

    public $store_id;
    public $store_name;
    public $merchant_id;
    public $merchant_name;
    public $mobile;

    public function rules(){
        return [
            [['page', 'store_id', 'merchant_id'], 'integer'],
            [['store_name', 'merchant_name', 'mobile'], 'trim']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $shop = new SmartShop();

            $wheres = [
                "s.status='1' AND m.copy<>0"
            ];

            if($this->store_id){
                $wheres[] = "s.id='{$this->store_id}'";
            }

            if($this->store_name){
                $wheres[] = "s.title LIKE '%{$this->store_name}%'";
            }

            if($this->merchant_id){
                $wheres[] = "m.id='{$this->merchant_id}'";
            }

            if($this->merchant_name){
                $wheres[] = "m.name LIKE '%{$this->merchant_name}%'";
            }

            if($this->mobile){
                $wheres[] = "m.mobile LIKE '%{$this->mobile}%'";
            }

            $selects = ["s.id as store_id", "s.title as store_name", "s.address", "pv.city_name as province",
                "ct.city_name as city", "s_at.filepath as store_logo", "m.id as merchant_id", "m.name as merchant_name",
                "m.mobile"];
            $list = $shop->getStoreList($pagination, $selects, $wheres, $this->page, $this->limit);
            foreach($list as &$item){
                $item['store_logo'] = rtrim($shop->setting['host_url'], "/") . str_replace("\\", "/", $item['store_logo']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list,
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