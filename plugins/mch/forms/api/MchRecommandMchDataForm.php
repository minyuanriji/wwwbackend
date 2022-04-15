<?php

namespace app\plugins\mch\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\plugins\mch\models\Mch;

class MchRecommandMchDataForm extends BaseModel implements ICacheForm {

    public $mch_ids;

    public function rules(){
        return [
            [['mch_ids'], 'required']
        ];
    }

    /**
     * @return array
     */
    public function getCacheKey() {
        $mchIds = $this->mch_ids ? $this->mch_ids : [];
        sort($mchIds);
        $keys[] = md5("mch:" . implode($mchIds));
        return $keys;
    }

    public function getSourceDataForm(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Store::find()->alias("s")
                ->innerJoin(["m" => Mch::tableName()], "m.id=s.mch_id");
            $query->andWhere([
                "AND",
                ["m.review_status" => Mch::REVIEW_STATUS_CHECKED],
                ["m.is_delete" => 0],
                ["IN", "m.id", $this->mch_ids]
            ]);
            $selects = ["s.id as store_id", "s.mch_id", "s.mall_id", "s.cover_url", "s.name", "s.address", "s.province_id", "s.city_id", "s.district_id", "s.business_hours"];

            //统计商品数量
            $selects[] = "(SELECT COUNT(*) FROM {{%goods}} WHERE mch_id=m.id AND status=1 AND is_delete=0 AND is_recycle=0) as goods_num";

            //统计销售数量
            $selects[] = "(SELECT COUNT(*) FROM {{%order}} WHERE mch_id=m.id AND is_pay=1 AND cancel_status=0 AND is_delete=0 AND is_recycle=0) as order_num";

            $query->select($selects);

            $list = $query->asArray()->all();
            if($list){
                foreach($list as $key => $item){
                    if(!preg_match("/^https?:\/\//i", trim($item['cover_url']))){
                        $item['cover_url'] =  $this->host_info . "/web/static/header-logo.png";
                    }
                    $goodsList = Goods::find()->alias('g')
                        ->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id")
                        ->andWhere([
                            'g.is_delete'  => 0,
                            'g.is_recycle' => 0,
                            'g.status'     => 1,
                            'g.mall_id'    => $item['mall_id'],
                            //'g.mch_id'     => $item['mch_id']
                        ])->select(["g.id", "gw.cover_pic", "g.price"])
                        ->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
                        ->groupBy('g.goods_warehouse_id')->limit(3)
                        ->asArray()->all();
                    $item['goods_list'] = $goodsList;
                    $list[$key] = $item;
                }
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        "list" => $list ? $list : []
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }


}