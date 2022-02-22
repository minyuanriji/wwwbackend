<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\helpers\PoiHelper;
use app\models\BaseModel;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Merchant;
use app\plugins\smart_shop\models\MerchantFzlist;

class ShopListForm extends BaseModel implements ICacheForm {

    public $limit = 10;
    public $page;
    public $mall_id;
    public $lng;
    public $lat;
    public $keyword;
    public $plat;

    public function rules() {
        return [
            [['page', 'limit'], 'integer'],
            [['lat', 'lng', 'keyword', 'plat'], 'trim']
        ];
    }

    public function getCacheKey(){
        return [(int)$this->page, (int)$this->limit, (int)$this->mall_id, $this->keyword, $this->lng, $this->lat];
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

            if($this->plat == "wechat"){
                $wheres[] = "wx_me.mp_appid IS NOT NULL AND wx_me.mp_appid <> ''";
            }elseif($this->plat == "alipay"){
                $wheres[] = "ali_me.ali_appid IS NOT NULL AND ali_me.ali_appid <> ''";
            }else{
                throw new \Exception("参数plat错误");
            }

            if(!empty($this->keyword)){
                $wheres[] = "s.title LIKE '%".$this->keyword."%'";
            }

            $selects = ["s.id as store_id", "s.title as store_name", "s.address", "pv.city_name as province",
                "ct.city_name as city", "s_at.filepath as store_logo", "m.id as merchant_id", "m.name as merchant_name",
                "m.mobile", "sst.coordinates", "wx_me.mp_appid as wx_mp_appid", "ali_me.ali_appid as ali_mp_appid"];

            $list = $shop->getStoreList($pagination, $selects, $wheres, $this->page, $this->limit);
            $defaultLogo = $this->host_info . "/web/static/header-logo.png";
            foreach($list as $key => $item){
                $item['sales']      = 0;
                $item['distance']   = -1;
                if(!empty($this->lat) && !empty($this->lng) && $item['coordinates']){
                    $coord = explode(",", $item['coordinates']);
                    $item['distance'] = (int)PoiHelper::getDistance($this->lng, $this->lat, $coord[1], $coord[0]);
                }
                $item['store_logo'] = !empty($item['store_logo']) ? rtrim($shop->setting['host_url'], "/") . str_replace("\\", "/", $item['store_logo']) : $defaultLogo;

                $list[$key] = $item;
            }

            //设置购物券
            $this->setShoppingVoucher($list);

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

    /**
     * 设置赠送购物券信息
     * @param $list
     */
    private function setShoppingVoucher(&$list){
        if(!$list) return;
        $storeIds = [];
        foreach($list as $item){
            $storeIds[] = $item['store_id'];
        }

        $tmps = MerchantFzlist::find()->alias("mfl")
            ->innerJoin(["mf" => Merchant::tableName()], "mfl.bsh_mch_id=mf.bsh_mch_id AND mf.is_delete=0")
            ->innerJoin(["m" => Mch::tableName()], "m.id=mf.bsh_mch_id")
            ->innerJoin(["s" => Store::tableName()], "s.mch_id=m.id")
            ->innerJoin(["svfs" => ShoppingVoucherFromStore::tableName()], "svfs.store_id=s.id AND svfs.is_delete=0")
            ->select(["mfl.ss_store_id", "mfl.bsh_mch_id", "m.transfer_rate", "svfs.give_value as shopping_voucher_give_value"])
            ->andWhere([
                "AND",
                ["mfl.is_delete"   => 0],
                ["m.is_delete"     => 0],
                ["m.review_status" => Mch::REVIEW_STATUS_CHECKED],
                ["IN", "mfl.ss_store_id", $storeIds]
            ])->asArray()->all();
        $rows = [];
        if($tmps){
            foreach($tmps as $tmp){
                $rows[$tmp['ss_store_id']] = $tmp;
            }
        }

        foreach($list as $key => $item){
            $item['shopping_voucher_remark'] = "";
            if(isset($rows[$item['store_id']])){
                $shoppingVoucherGiveValue = $rows[$item['store_id']]['shopping_voucher_give_value'];
                if($shoppingVoucherGiveValue){
                    $item['shopping_voucher_remark'] = "付100送".$shoppingVoucherGiveValue."红包";
                }
            }
            $list[$key] = $item;
        }
    }
}