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

class MchGoodsListForm extends BaseModel implements ICacheForm {

    public $page;
    public $store_id;

    public function rules(){
        return [
            [['store_id'], 'required'],
            [['page'], 'default', 'value' => 1]
        ];
    }


    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $store = Store::findOne($this->store_id);
            if(!$store || $store->is_delete){
                throw new \Exception("门店不存在");
            }

            $mch = Mch::findOne($store->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户不存在");
            }

            $query = Goods::find()->alias('g');
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->andWhere([
                'g.is_delete'  => 0,
                'g.is_recycle' => 0,
//                'g.status'     => 1,//上下架都显示
                'g.mall_id'    => $store->mall_id,
                'g.mch_id'     => $mch->id
            ]);
            $selects = ["g.id", "g.goods_warehouse_id", "gw.name", "gw.cover_pic", "gw.original_price", "g.price", "gw.unit"];
            $query->select($selects);
            $list = $query->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
                          ->groupBy('g.goods_warehouse_id')
                          ->page($pagination, 10, $this->page)
                          ->asArray()
                          ->all();
            if($list){
                foreach($list as &$item){

                }
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'list' => $list ? $list : [],
                        'pagination' => $pagination,
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return ['code' => ApiCode::CODE_FAIL, 'msg' => $e->getMessage()];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        return [$this->store_id, $this->page];
    }
}