<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\smart_shop\components\SmartShop;

class ShopListForm extends BaseModel implements ICacheForm {

    public $limit = 10;
    public $page;
    public $mall_id;

    public function rules() {
        return [
            [['page', 'limit'], 'integer']
        ];
    }

    public function getCacheKey(){
        return [(int)$this->page, (int)$this->limit, (int)$this->mall_id];
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $shop = new SmartShop();

            $wheres = [
                "s.status='1' AND m.copy<>0"
            ];

            $selects = ["s.id as store_id", "s.title as store_name", "s.address", "pv.city_name as province",
                "ct.city_name as city", "s_at.filepath as store_logo", "m.id as merchant_id", "m.name as merchant_name",
                "m.mobile"];
            $list = $shop->getStoreList($pagination, $selects, $wheres, $this->page, $this->limit);
            $defaultLogo = $this->host_info . "/web/static/header-logo.png";
            foreach($list as &$item){
                $item['store_logo'] = !empty($item['store_logo']) ? rtrim($shop->setting['host_url'], "/") . str_replace("\\", "/", $item['store_logo']) : $defaultLogo;
            }

            $sourceData = $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                    'list'        => $list ? $list : [],
                    'page_count'  => isset($pagination['page_count']) ? $pagination['page_count'] : 0,
                    'total_count' => isset($pagination['total_count']) ? $pagination['total_count'] : 0
                ]
            );

            return new APICacheDataForm([
                "sourceData" => $sourceData
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}